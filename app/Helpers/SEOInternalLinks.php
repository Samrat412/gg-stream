<?php
/**
 * SEO Internal Links Generator
 * Generates internal linking structure for SEO
 */

namespace App\Helpers;

use App\Services\TMDBService;

class SEOInternalLinks {
    private TMDBService $tmdb;
    
    public function __construct() {
        $this->tmdb = new TMDBService();
    }
    
    /**
     * Generate all internal links for a media item
     */
    public function generateInternalLinks(array $media, bool $isTV): array {
        $links = [
            'related' => [],
            'sameGenre' => [],
            'trending' => [],
            'sameYear' => [],
            'collection' => []
        ];
        
        // Get related from similar/recommendations
        $similar = $media['similar']['results'] ?? [];
        $recommendations = $media['recommendations']['results'] ?? [];
        $combined = array_merge($similar, $recommendations);
        
        // Deduplicate and limit to 8
        $seen = [];
        foreach ($combined as $item) {
            if (count($links['related']) >= 8) break;
            $id = $item['id'];
            if (!isset($seen[$id])) {
                $seen[$id] = true;
                $links['related'][] = [
                    'id' => $id,
                    'title' => $item['title'] ?? $item['name'],
                    'type' => $isTV ? 'tv' : 'movie',
                    'poster' => $item['poster_path'],
                    'year' => substr($item['release_date'] ?? $item['first_air_date'] ?? '', 0, 4)
                ];
            }
        }
        
        // Fill remaining from same genre
        if (count($links['related']) < 8) {
            $genres = $media['genres'] ?? [];
            if (!empty($genres)) {
                $genreId = $genres[0]['id'];
                $genreResults = $this->tmdb->discoverByGenre($isTV ? 'tv' : 'movie', $genreId, 1);
                
                foreach ($genreResults['results'] ?? [] as $item) {
                    if (count($links['related']) >= 8) break;
                    if ($item['id'] !== $media['id']) {
                        $links['sameGenre'][] = [
                            'id' => $item['id'],
                            'title' => $item['title'] ?? $item['name'],
                            'type' => $isTV ? 'tv' : 'movie',
                            'poster' => $item['poster_path'],
                            'year' => substr($item['release_date'] ?? $item['first_air_date'] ?? '', 0, 4)
                        ];
                    }
                }
            }
        }
        
        // Get trending
        $trending = $this->tmdb->fetchTrending($isTV ? 'tv' : 'movie', 'week');
        foreach ($trending['results'] ?? [] as $item) {
            if (count($links['trending']) >= 5) break;
            if ($item['id'] !== $media['id']) {
                $links['trending'][] = [
                    'id' => $item['id'],
                    'title' => $item['title'] ?? $item['name'],
                    'type' => $isTV ? 'tv' : 'movie',
                    'poster' => $item['poster_path'],
                    'year' => substr($item['release_date'] ?? $item['first_air_date'] ?? '', 0, 4)
                ];
            }
        }
        
        // Get same year
        $year = substr($media['release_date'] ?? $media['first_air_date'] ?? '', 0, 4);
        if (!empty($year)) {
            $endpoint = $isTV ? '/discover/tv' : '/discover/movie';
            $yearResults = $this->tmdb->fetch($endpoint, [
                'primary_release_year' => $year,
                'sort_by' => 'popularity.desc',
                'page' => 1
            ]);
            
            foreach ($yearResults['results'] ?? [] as $item) {
                if (count($links['sameYear']) >= 5) break;
                if ($item['id'] !== $media['id']) {
                    $links['sameYear'][] = [
                        'id' => $item['id'],
                        'title' => $item['title'] ?? $item['name'],
                        'type' => $isTV ? 'tv' : 'movie',
                        'poster' => $item['poster_path'],
                        'year' => $year
                    ];
                }
            }
        }
        
        // Get collection (movies only)
        if (!$isTV && !empty($media['belongs_to_collection'])) {
            $collection = $media['belongs_to_collection'];
            $collectionDetails = $this->tmdb->fetchCollectionDetails($collection['id']);
            
            foreach ($collectionDetails['parts'] ?? [] as $item) {
                if (count($links['collection']) >= 8) break;
                if ($item['id'] !== $media['id']) {
                    $links['collection'][] = [
                        'id' => $item['id'],
                        'title' => $item['title'],
                        'type' => 'movie',
                        'poster' => $item['poster_path'],
                        'year' => substr($item['release_date'] ?? '', 0, 4)
                    ];
                }
            }
        }
        
        return $links;
    }
    
    /**
     * Generate category/browse links
     */
    public function getCategoryLinks(array $media, bool $isTV): array {
        $links = [
            ['title' => 'Browse Movies', 'url' => '/browse/movies'],
            ['title' => 'Browse TV Shows', 'url' => '/browse/tv'],
            ['title' => 'Browse Anime', 'url' => '/browse/anime'],
            ['title' => 'Trending Now', 'url' => '/'],
            ['title' => 'Top Rated', 'url' => $isTV ? '/tv/top-rated' : '/movie/top-rated'],
        ];
        
        // Add genre links
        foreach ($media['genres'] ?? [] as $genre) {
            $slug = slugify($genre['name']);
            $links[] = [
                'title' => $genre['name'] . ' Movies',
                'url' => "/movies/{$slug}"
            ];
        }
        
        return array_slice($links, 0, 15);
    }
}
