<?php
/**
 * Content Card Partial
 * Reusable card component for movies and TV shows
 */

$media = $media ?? [];
$mediaType = $mediaType ?? 'movie';
$title = $title ?? ($media['title'] ?? $media['name'] ?? '');
$year = $year ?? getYear($media['release_date'] ?? $media['first_air_date'] ?? '');
$rating = $rating ?? ($media['vote_average'] ?? 0);
$poster = $poster ?? ($media['poster_path'] ?? '');
$id = $id ?? ($media['id'] ?? 0);
?>

<a href="/<?= $mediaType ?>/<?= $id ?>" class="content-card" data-tmdb-id="<?= $id ?>" data-type="<?= $mediaType ?>">
    <div class="card-poster">
        <img src="<?= $tmdb->getPosterUrl($poster) ?>" alt="<?= e($title) ?>" loading="lazy">
        <div class="card-overlay">
            <h3 class="card-title"><?= e($title) ?></h3>
            <div class="card-meta">
                <span><?= $year ?></span>
                <span class="card-rating">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <?= number_format($rating, 1) ?>
                </span>
                <?php if (!empty($media)): ?>
                <span class="card-quality"><?= detectQuality($media) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</a>
