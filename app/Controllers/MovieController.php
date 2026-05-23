<?php
/**
 * Movie Controller
 * Movie detail pages
 */

namespace App\Controllers;

use App\Services\TMDBService;
use App\Helpers\SEOTitleGenerator;
use App\Helpers\SEODescriptionGenerator;
use App\Helpers\SEOSchemaGenerator;
use App\Helpers\SEOInternalLinks;
use App\Helpers\SEOContentGenerator;

class MovieController extends Controller {
    private TMDBService $tmdb;
    
    public function __construct() {
        $this->tmdb = new TMDBService();
    }
    
    public function detail(int $id): void {
        // Fetch movie details
        $movie = $this->tmdb->fetchMovieDetails($id);
        
        if (empty($movie) || isset($movie['success']) && !$movie['success']) {
            http_response_code(404);
            $this->redirect('/404');
            return;
        }
        
        setCacheHeaders(300);
        
        // Generate SEO elements
        $titleGenerator = new SEOTitleGenerator();
        $descGenerator = new SEODescriptionGenerator();
        $schemaGenerator = new SEOSchemaGenerator();
        $internalLinks = new SEOInternalLinks();
        $contentGenerator = new SEOContentGenerator();
        
        $title = $titleGenerator->generateUniqueTitle($movie, false);
        $description = $descGenerator->generateUniqueDescription($movie, false);
        $canonical = SITE_DOMAIN . "/movie/{$id}";
        
        // Generate structured data
        $schemas = $schemaGenerator->combineSchemas(
            $schemaGenerator->generateMovieSchema($movie, $canonical),
            $schemaGenerator->generateVideoObjectSchema($movie, false),
            $schemaGenerator->generateBreadcrumbSchema([
                ['name' => 'Home', 'url' => SITE_DOMAIN],
                ['name' => 'Movies', 'url' => SITE_DOMAIN . '/browse/movies'],
                ['name' => $movie['title'], 'url' => $canonical]
            ]),
            $schemaGenerator->generateWebPageSchema($title, $description, $canonical),
            $schemaGenerator->generateFAQSchema($movie, false)
        );
        
        // Generate internal links
        $relatedMovies = $internalLinks->generateInternalLinks($movie, false);
        
        // Generate SEO content (500+ words)
        $seoContent = [
            'intro' => $contentGenerator->generateIntroBlock($movie, false),
            'experience' => $contentGenerator->generateViewingExperience($movie, false),
            'genre' => $contentGenerator->generateGenreSpotlight($movie),
            'recommendation' => $contentGenerator->generateAudienceRecommendation($movie, false)
        ];
        
        // Prepare data for view
        $this->set('title', $title);
        $this->set('description', $description);
        $this->set('canonical', $canonical);
        $this->set('ogType', 'video.movie');
        $this->set('ogImage', $this->tmdb->getBackdropUrl($movie['backdrop_path'] ?? null));
        $this->set('keywords', $this->generateKeywords($movie));
        $this->set('structuredData', $schemas);
        
        $this->set('movie', $movie);
        $this->set('tmdb', $this->tmdb);
        $this->set('relatedMovies', $relatedMovies);
        $this->set('seoContent', $seoContent);
        
        $this->render('pages/movie-detail');
    }
    
    private function generateKeywords(array $movie): string {
        $genres = array_map(fn($g) => $g['name'], $movie['genres'] ?? []);
        $keywords = [
            $movie['title'] ?? '',
            'watch free',
            'stream online',
            'no signup',
            'HD',
            implode(', ', $genres)
        ];
        return implode(', ', array_filter($keywords));
    }
}
