<?php
/**
 * Home Controller
 * Homepage with trending content
 */

namespace App\Controllers;

use App\Services\TMDBService;

class HomeController extends Controller {
    private TMDBService $tmdb;
    
    public function __construct() {
        $this->tmdb = new TMDBService();
    }
    
    public function index(): void {
        setCacheHeaders(300);
        
        // Fetch trending content
        $trendingMovies = $this->tmdb->fetchTrending('movie', 'week');
        $trendingTV = $this->tmdb->fetchTrending('tv', 'week');
        
        // Fetch popular content
        $popularMovies = $this->tmdb->fetchPopularMovies();
        $popularTV = $this->tmdb->fetchPopularTV();
        
        // Fetch top rated
        $topRatedMovies = $this->tmdb->fetchTopRatedMovies();
        $topRatedTV = $this->tmdb->fetchTopRatedTV();
        
        // Fetch now playing
        $nowPlaying = $this->tmdb->fetchNowPlayingMovies();
        
        // Fetch upcoming
        $upcoming = $this->tmdb->fetchUpcomingMovies();
        
        // Fetch on the air TV
        $onTheAir = $this->tmdb->fetchOnTheAirTV();
        
        // Prepare data for view
        $this->set('title', SITE_NAME . ' - Watch Free Movies & TV Shows Online');
        $this->set('description', 'Watch free movies and TV shows online in HD. No signup required. Stream popular movies, classic films, anime series, and more.');
        $this->set('canonical', SITE_DOMAIN . '/');
        $this->set('ogType', 'website');
        $this->set('ogImage', null);
        
        $this->set('trendingMovies', $trendingMovies['results'] ?? []);
        $this->set('trendingTV', $trendingTV['results'] ?? []);
        $this->set('popularMovies', $popularMovies['results'] ?? []);
        $this->set('popularTV', $popularTV['results'] ?? []);
        $this->set('topRatedMovies', $topRatedMovies['results'] ?? []);
        $this->set('topRatedTV', $topRatedTV['results'] ?? []);
        $this->set('nowPlaying', $nowPlaying['results'] ?? []);
        $this->set('upcoming', $upcoming['results'] ?? []);
        $this->set('onTheAir', $onTheAir['results'] ?? []);
        
        $this->set('tmdb', $this->tmdb);
        
        $this->render('pages/home');
    }
}
