<?php
/**
 * TMDB Service
 * All TMDB API calls with caching - Server-Side Only
 */

class TMDBService {
    private string $apiKey;
    private string $baseUrl;
    private string $imageBase;
    private CacheService $cache;
    
    // Image sizes
    const POSTER_SMALL = '/w185';
    const POSTER_MEDIUM = '/w342';
    const POSTER_LARGE = '/w500';
    const POSTER_ORIGINAL = '/original';
    const BACKDROP_SMALL = '/w300';
    const BACKDROP_MEDIUM = '/w780';
    const BACKDROP_LARGE = '/w1280';
    const BACKDROP_ORIGINAL = '/original';
    const PROFILE_SMALL = '/w45';
    const PROFILE_MEDIUM = '/w185';
    const PROFILE_LARGE = '/h632';
    
    public function __construct() {
        $this->apiKey = TMDB_API_KEY;
        $this->baseUrl = TMDB_BASE_URL;
        $this->imageBase = TMDB_IMAGE_BASE;
        $this->cache = new CacheService();
    }
    
    /**
     * Core fetcher with caching
     */
    public function fetch(string $endpoint, array $params = [], int $cacheTTL = 300): array {
        $cacheKey = "tmdb:{$endpoint}:" . md5(http_build_query($params));
        
        return $this->cache->remember($cacheKey, function() use ($endpoint, $params) {
            $url = $this->baseUrl . $endpoint;
            $params['api_key'] = $this->apiKey;
            
            $fullUrl = $url . '?' . http_build_query($params);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || $response === false) {
                error_log("TMDB API Error: HTTP $httpCode for $endpoint");
                return [];
            }
            
            return json_decode($response, true) ?? [];
        }, $cacheTTL);
    }
    
    /**
     * Image URL builders
     */
    public function getPosterUrl(?string $path, string $size = 'large'): string {
        if (!$path) {
            return '/images/placeholder-poster.svg';
        }
        
        $sizeMap = [
            'small' => self::POSTER_SMALL,
            'medium' => self::POSTER_MEDIUM,
            'large' => self::POSTER_LARGE,
            'original' => self::POSTER_ORIGINAL
        ];
        
        return $this->imageBase . ($sizeMap[$size] ?? self::POSTER_LARGE) . $path;
    }
    
    public function getBackdropUrl(?string $path, string $size = 'original'): string {
        if (!$path) {
            return '/images/placeholder-backdrop.svg';
        }
        
        $sizeMap = [
            'small' => self::BACKDROP_SMALL,
            'medium' => self::BACKDROP_MEDIUM,
            'large' => self::BACKDROP_LARGE,
            'original' => self::BACKDROP_ORIGINAL
        ];
        
        return $this->imageBase . ($sizeMap[$size] ?? self::BACKDROP_ORIGINAL) . $path;
    }
    
    public function getProfileUrl(?string $path, string $size = 'medium'): string {
        if (!$path) {
            return '/images/placeholder-profile.svg';
        }
        
        $sizeMap = [
            'small' => self::PROFILE_SMALL,
            'medium' => self::PROFILE_MEDIUM,
            'large' => self::PROFILE_LARGE
        ];
        
        return $this->imageBase . ($sizeMap[$size] ?? self::PROFILE_MEDIUM) . $path;
    }
    
    /**
     * Movie endpoints
     */
    public function fetchTrending(string $mediaType = 'all', string $timeWindow = 'week'): array {
        return $this->fetch("/trending/{$mediaType}/{$timeWindow}", [], CACHE_TTL_DISCOVER);
    }
    
    public function fetchPopularMovies(int $page = 1): array {
        return $this->fetch('/movie/popular', ['page' => $page], CACHE_TTL_DISCOVER);
    }
    
    public function fetchTopRatedMovies(int $page = 1): array {
        return $this->fetch('/movie/top_rated', ['page' => $page], CACHE_TTL_DISCOVER);
    }
    
    public function fetchNowPlayingMovies(int $page = 1): array {
        return $this->fetch('/movie/now_playing', ['page' => $page], CACHE_TTL_DISCOVER);
    }
    
    public function fetchUpcomingMovies(int $page = 1): array {
        return $this->fetch('/movie/upcoming', ['page' => $page], CACHE_TTL_DISCOVER);
    }
    
    public function fetchMovieDetails(int $id): array {
        return $this->fetch("/movie/{$id}", [
            'append_to_response' => 'credits,videos,similar,recommendations'
        ], CACHE_TTL_DETAILS);
    }
    
    /**
     * TV endpoints
     */
    public function fetchPopularTV(int $page = 1): array {
        return $this->fetch('/tv/popular', ['page' => $page], CACHE_TTL_DISCOVER);
    }
    
    public function fetchTopRatedTV(int $page = 1): array {
        return $this->fetch('/tv/top_rated', ['page' => $page], CACHE_TTL_DISCOVER);
    }
    
    public function fetchOnTheAirTV(int $page = 1): array {
        return $this->fetch('/tv/on_the_air', ['page' => $page], CACHE_TTL_DISCOVER);
    }
    
    public function fetchTVDetails(int $id): array {
        return $this->fetch("/tv/{$id}", [
            'append_to_response' => 'credits,videos,similar,recommendations,external_ids'
        ], CACHE_TTL_DETAILS);
    }
    
    public function fetchTVSeason(int $tvId, int $seasonNumber): array {
        return $this->fetch("/tv/{$tvId}/season/{$seasonNumber}", [], CACHE_TTL_DETAILS);
    }
    
    /**
     * Discover endpoints
     */
    public function discoverByGenre(string $type, int $genreId, int $page = 1): array {
        $endpoint = $type === 'movie' ? '/discover/movie' : '/discover/tv';
        return $this->fetch($endpoint, [
            'with_genres' => $genreId,
            'page' => $page,
            'sort_by' => 'popularity.desc'
        ], CACHE_TTL_DISCOVER);
    }
    
    public function discoverAnime(string $mediaType = 'tv', int $page = 1, string $sortBy = 'popularity.desc', array $extraParams = []): array {
        $endpoint = $mediaType === 'movie' ? '/discover/movie' : '/discover/tv';
        $params = array_merge([
            'with_genres' => 16,
            'with_origin_country' => 'JP',
            'page' => $page,
            'sort_by' => $sortBy
        ], $extraParams);
        
        return $this->fetch($endpoint, $params, CACHE_TTL_DISCOVER);
    }
    
    public function fetchPopularAnimeTV(int $page = 1): array {
        return $this->discoverAnime('tv', $page, 'popularity.desc');
    }
    
    public function fetchTopRatedAnimeTV(int $page = 1): array {
        return $this->discoverAnime('tv', $page, 'vote_average.desc');
    }
    
    public function fetchPopularAnimeMovies(int $page = 1): array {
        return $this->discoverAnime('movie', $page, 'popularity.desc');
    }
    
    /**
     * Search endpoints
     */
    public function searchMulti(string $query, int $page = 1): array {
        return $this->fetch('/search/multi', ['query' => $query, 'page' => $page], CACHE_TTL_SEARCH);
    }
    
    public function searchPerson(string $query): array {
        return $this->fetch('/search/person', ['query' => $query], CACHE_TTL_SEARCH);
    }
    
    public function searchCompany(string $query): array {
        return $this->fetch('/search/company', ['query' => $query], CACHE_TTL_SEARCH);
    }
    
    public function searchCollection(string $query): array {
        return $this->fetch('/search/collection', ['query' => $query], CACHE_TTL_SEARCH);
    }
    
    public function searchMovie(string $query, int $page = 1): array {
        return $this->fetch('/search/movie', ['query' => $query, 'page' => $page], CACHE_TTL_SEARCH);
    }
    
    /**
     * Person/Entity endpoints
     */
    public function fetchPersonCredits(int $personId): array {
        return $this->fetch("/person/{$personId}/combined_credits", [], CACHE_TTL_PERSON);
    }
    
    public function fetchCollectionDetails(int $collectionId): array {
        return $this->fetch("/collection/{$collectionId}", [], CACHE_TTL_DISCOVER);
    }
    
    /**
     * Embed URLs
     */
    public function getMovieEmbedUrl(int $tmdbId, int $server = 1): string {
        if ($server === 1) {
            return "https://" . PRIMARY_EMBED_DOMAIN . "/v2/embed/movie/{$tmdbId}";
        }
        return "https://" . SECONDARY_EMBED_DOMAIN . "/movie/{$tmdbId}";
    }
    
    public function getTVEmbedUrl(int $tmdbId, int $season, int $episode, int $server = 1): string {
        if ($server === 1) {
            return "https://" . PRIMARY_EMBED_DOMAIN . "/v2/embed/tv/{$tmdbId}/{$season}/{$episode}";
        }
        return "https://" . SECONDARY_EMBED_DOMAIN . "/tv/{$tmdbId}/{$season}/{$episode}";
    }
}
