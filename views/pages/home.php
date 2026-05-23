<?php
/**
 * Homepage View
 * Prime Video style homepage with content rows
 */

$tmdb = $tmdb ?? new App\Services\TMDBService();
?>

<!-- Hero Section -->
<section class="hero">
    <?php if (!empty($trendingMovies['results'][0])): ?>
        <?php $featured = $trendingMovies['results'][0]; ?>
        <div class="hero-backdrop" style="background-image: url('<?= $tmdb->getBackdropUrl($featured['backdrop_path']) ?>')"></div>
        <div class="hero-gradient"></div>
        <div class="hero-content">
            <div class="hero-info">
                <h1 class="hero-title"><?= e($featured['title'] ?? $featured['name']) ?></h1>
                <div class="hero-meta">
                    <span><?= getYear($featured['release_date'] ?? $featured['first_air_date'] ?? '') ?></span>
                    <span class="card-rating">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <?= number_format($featured['vote_average'] ?? 0, 1) ?>
                    </span>
                    <span><?= detectQuality($featured) ?></span>
                </div>
                <p class="hero-description"><?= truncate($featured['overview'] ?? '', 200) ?></p>
                <div class="hero-buttons">
                    <a href="/watch/movie/<?= $featured['id'] ?>" class="btn btn-primary btn-lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        Watch Now
                    </a>
                    <a href="/movie/<?= $featured['id'] ?>" class="btn btn-secondary btn-lg">More Info</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- Content Rows -->
<div class="container">
    <!-- Trending Movies -->
    <?php if (!empty($trendingMovies['results'])): ?>
    <section class="content-row">
        <div class="content-row-header">
            <h2 class="row-title">Trending Movies This Week</h2>
            <a href="/browse/movies" class="row-link">View All →</a>
        </div>
        <div class="content-row-scroller" id="trending-movies">
            <button class="scroll-arrow prev" aria-label="Scroll left">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <div class="content-row-track">
                <?php foreach ($trendingMovies['results'] as $movie): ?>
                    <?php include __DIR__ . '/partials/content-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <button class="scroll-arrow next" aria-label="Scroll right">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Trending TV -->
    <?php if (!empty($trendingTV['results'])): ?>
    <section class="content-row">
        <div class="content-row-header">
            <h2 class="row-title">Trending TV Shows This Week</h2>
            <a href="/browse/tv" class="row-link">View All →</a>
        </div>
        <div class="content-row-scroller" id="trending-tv">
            <button class="scroll-arrow prev" aria-label="Scroll left">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <div class="content-row-track">
                <?php foreach ($trendingTV['results'] as $show): ?>
                    <?php 
                    $media = $show;
                    $mediaType = 'tv';
                    $title = $show['name'] ?? '';
                    $year = getYear($show['first_air_date'] ?? '');
                    $rating = $show['vote_average'] ?? 0;
                    $poster = $show['poster_path'] ?? '';
                    ?>
                    <a href="/tv/<?= $show['id'] ?>" class="content-card" data-tmdb-id="<?= $show['id'] ?>" data-type="tv">
                        <div class="card-poster">
                            <img src="<?= $tmdb->getPosterUrl($poster) ?>" alt="<?= e($title) ?>" loading="lazy">
                            <div class="card-overlay">
                                <h3 class="card-title"><?= e($title) ?></h3>
                                <div class="card-meta">
                                    <span><?= $year ?></span>
                                    <span class="card-rating">
                                        ★ <?= number_format($rating, 1) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="scroll-arrow next" aria-label="Scroll right">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Popular Movies -->
    <?php if (!empty($popularMovies['results'])): ?>
    <section class="content-row">
        <div class="content-row-header">
            <h2 class="row-title">Popular Movies</h2>
            <a href="/browse/movies" class="row-link">View All →</a>
        </div>
        <div class="content-row-scroller" id="popular-movies">
            <button class="scroll-arrow prev" aria-label="Scroll left"></button>
            <div class="content-row-track">
                <?php foreach ($popularMovies['results'] as $movie): ?>
                    <?php 
                    $media = $movie;
                    $mediaType = 'movie';
                    $title = $movie['title'] ?? '';
                    $year = getYear($movie['release_date'] ?? '');
                    $rating = $movie['vote_average'] ?? 0;
                    $poster = $movie['poster_path'] ?? '';
                    ?>
                    <a href="/movie/<?= $movie['id'] ?>" class="content-card" data-tmdb-id="<?= $movie['id'] ?>" data-type="movie">
                        <div class="card-poster">
                            <img src="<?= $tmdb->getPosterUrl($poster) ?>" alt="<?= e($title) ?>" loading="lazy">
                            <div class="card-overlay">
                                <h3 class="card-title"><?= e($title) ?></h3>
                                <div class="card-meta">
                                    <span><?= $year ?></span>
                                    <span class="card-rating">★ <?= number_format($rating, 1) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="scroll-arrow next" aria-label="Scroll right"></button>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Top Rated Movies -->
    <?php if (!empty($topRatedMovies['results'])): ?>
    <section class="content-row">
        <div class="content-row-header">
            <h2 class="row-title">Top Rated Movies</h2>
            <a href="/movies/top-rated" class="row-link">View All →</a>
        </div>
        <div class="content-row-scroller">
            <div class="content-row-track">
                <?php foreach (array_slice($topRatedMovies['results'], 0, 12) as $movie): ?>
                    <?php 
                    $media = $movie;
                    $mediaType = 'movie';
                    $title = $movie['title'] ?? '';
                    $year = getYear($movie['release_date'] ?? '');
                    $rating = $movie['vote_average'] ?? 0;
                    $poster = $movie['poster_path'] ?? '';
                    ?>
                    <a href="/movie/<?= $movie['id'] ?>" class="content-card">
                        <div class="card-poster">
                            <img src="<?= $tmdb->getPosterUrl($poster) ?>" alt="<?= e($title) ?>" loading="lazy">
                            <div class="card-overlay">
                                <h3 class="card-title"><?= e($title) ?></h3>
                                <div class="card-meta">
                                    <span><?= $year ?></span>
                                    <span class="card-rating">★ <?= number_format($rating, 1) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- SEO Content Block -->
<div class="container mt-3 mb-3">
    <article class="seo-content">
        <h2>Watch Free Movies and TV Shows Online</h2>
        <p>Welcome to <?= SITE_NAME ?>, your ultimate destination for free streaming entertainment. Discover thousands of movies, TV shows, and anime series available to watch online without any registration or subscription fees.</p>
        <p>Our platform offers HD quality streaming with no ads interruptions. Whether you're looking for the latest blockbusters, classic films, binge-worthy TV series, or popular anime, we have something for everyone. Browse by genre, year, or popularity to find your next favorite show.</p>
        <h3>Why Choose <?= SITE_NAME ?>?</h3>
        <ul>
            <li>✓ Completely free - no signup required</li>
            <li>✓ HD and Full HD streaming quality</li>
            <li>✓ No advertisements or pop-ups</li>
            <li>✓ Regular updates with new content</li>
            <li>✓ Works on all devices - desktop, mobile, tablet</li>
            <li>✓ Fast streaming with minimal buffering</li>
        </ul>
    </article>
</div>
