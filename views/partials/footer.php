<?php
/**
 * Footer Partial
 * Prime Video style footer with SEO links
 */
?>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-section">
                <h4>Browse</h4>
                <div class="footer-links">
                    <a href="/browse/movies" class="footer-link">Movies</a>
                    <a href="/browse/tv" class="footer-link">TV Shows</a>
                    <a href="/browse/anime" class="footer-link">Anime</a>
                    <a href="/" class="footer-link">Trending Now</a>
                    <a href="/collections" class="footer-link">Collections</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Genres</h4>
                <div class="footer-links">
                    <a href="/movies/action" class="footer-link">Action Movies</a>
                    <a href="/movies/comedy" class="footer-link">Comedy Movies</a>
                    <a href="/movies/drama" class="footer-link">Drama Movies</a>
                    <a href="/movies/horror" class="footer-link">Horror Movies</a>
                    <a href="/movies/sci-fi" class="footer-link">Sci-Fi Movies</a>
                    <a href="/movies/thriller" class="footer-link">Thriller Movies</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Top Lists</h4>
                <div class="footer-links">
                    <a href="/best/best-movies-2026" class="footer-link">Best Movies 2026</a>
                    <a href="/top/action-movies" class="footer-link">Top Action Movies</a>
                    <a href="/best/anime-series" class="footer-link">Best Anime Series</a>
                    <a href="/best/sci-fi-movies" class="footer-link">Top Sci-Fi Films</a>
                    <a href="/editorial" class="footer-link">All Top Lists</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Entities</h4>
                <div class="footer-links">
                    <a href="/actors" class="footer-link">Popular Actors</a>
                    <a href="/directors" class="footer-link">Top Directors</a>
                    <a href="/studios" class="footer-link">Major Studios</a>
                    <a href="/collections" class="footer-link">Movie Collections</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Community</h4>
                <div class="footer-links">
                    <a href="/requests" class="footer-link">Request Content</a>
                    <a href="/my-list" class="footer-link">My List</a>
                    <a href="/about" class="footer-link">About Us</a>
                    <a href="/contact" class="footer-link">Contact</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Legal</h4>
                <div class="footer-links">
                    <a href="/privacy-policy" class="footer-link">Privacy Policy</a>
                    <a href="/terms" class="footer-link">Terms of Service</a>
                    <a href="/dmca" class="footer-link">DMCA</a>
                    <a href="/sitemap.xml" class="footer-link">Sitemap</a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
            <p class="text-muted">This site does not host any video content. All content is provided by third-party embed sources.</p>
        </div>
    </div>
</footer>
