<?php
/**
 * Navbar Partial
 * Prime Video style navigation
 */

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<nav class="navbar">
    <div class="navbar-content">
        <div class="navbar-left">
            <a href="/" class="logo"><?= SITE_NAME ?></a>
            
            <div class="nav-links">
                <a href="/browse/movies" class="nav-link <?= str_starts_with($currentPath, '/browse/movies') ? 'active' : '' ?>">Movies</a>
                <a href="/browse/tv" class="nav-link <?= str_starts_with($currentPath, '/browse/tv') ? 'active' : '' ?>">TV Shows</a>
                <a href="/browse/anime" class="nav-link <?= str_starts_with($currentPath, '/browse/anime') ? 'active' : '' ?>">Anime</a>
                <a href="/editorial" class="nav-link <?= str_starts_with($currentPath, '/editorial') || str_starts_with($currentPath, '/best') || str_starts_with($currentPath, '/top') ? 'active' : '' ?>">Top Lists</a>
            </div>
        </div>
        
        <div class="navbar-right">
            <form action="/search" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Search movies, TV shows..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="search-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </form>
            
            <a href="/my-list" class="nav-link">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </a>
        </div>
    </div>
</nav>
