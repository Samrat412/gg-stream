<?php
/**
 * SEO Description Generator
 * Generates unique, compelling meta descriptions
 */

namespace App\Helpers;

class SEODescriptionGenerator {
    private const INTRO_PHRASES = [
        'Watch', 'Stream', 'Enjoy', 'Experience', 'Discover',
        'Dive into', 'Immerse yourself in', 'Get ready for',
        'Don\'t miss', 'Start watching'
    ];
    
    private const QUALITY_DESCRIPTORS = [
        'stunning HD', 'crystal-clear Full HD', 'breathtaking 4K',
        'high-quality', 'premium HD', 'sharp HD', 'brilliant HD',
        'exceptional HD', 'superior quality', 'immersive HD'
    ];
    
    private const EXPERIENCE_TERMS = [
        'thrilling adventure', 'captivating story', 'unforgettable journey',
        'gripping tale', 'compelling narrative', 'exciting experience',
        'dramatic storyline', 'engaging plot', 'mesmerizing film',
        'powerful performance', 'stunning visuals', 'masterful direction'
    ];
    
    private const CONTENT_DESCRIPTORS = [
        'Full movie streaming', 'Complete series available', 'All episodes ready',
        'No registration required', 'Instant access', 'Free forever',
        'No credit card needed', 'Unlimited streaming', 'Ad-free experience',
        'Multiple quality options', 'Fast loading', 'Mobile compatible'
    ];
    
    private const CALL_TO_ACTION = [
        'Start watching now!', 'Stream free today!', 'Watch without signup!',
        'Click to play now!', 'Enjoy free streaming!', 'Begin your viewing!',
        'Stream instantly!', 'Watch in HD now!', 'Play free online!'
    ];
    
    /**
     * Generate unique description
     */
    public function generateUniqueDescription(array $media, bool $isTV): string {
        $title = $media['title'] ?? $media['name'] ?? '';
        $overview = $media['overview'] ?? '';
        $year = $this->extractYear($media);
        $genres = $this->extractGenres($media);
        $quality = detectQuality($media);
        
        // Deterministic selection based on hash
        $hash = hashCode($title . '_desc');
        
        // Build description from components
        $intro = self::INTRO_PHRASES[abs($hash) % count(self::INTRO_PHRASES)];
        $qualityDesc = self::QUALITY_DESCRIPTORS[abs($hash + 1) % count(self::QUALITY_DESCRIPTORS)];
        $experience = self::EXPERIENCE_TERMS[abs($hash + 2) % count(self::EXPERIENCE_TERMS)];
        $content = self::CONTENT_DESCRIPTORS[abs($hash + 3) % count(self::CONTENT_DESCRIPTORS)];
        $cta = self::CALL_TO_ACTION[abs($hash + 4) % count(self::CALL_TO_ACTION)];
        
        // Create base description
        if ($isTV) {
            $base = "{$intro} {$title} ({$year}) - {$experience} with {$qualityDesc} quality. " .
                    "Free TV series streaming with all seasons and episodes. {$content}. {$cta}";
        } else {
            $base = "{$intro} {$title} ({$year}) - {$experience} in {$qualityDesc}. " .
                    "Watch full movie free online without registration. {$content}. {$cta}";
        }
        
        // Add genre info if available
        if (!empty($genres)) {
            $genreStr = is_array($genres) ? implode(', ', array_slice($genres, 0, 3)) : $genres;
            $base = str_replace('quality.', "quality. A {$genreStr} production featuring ", $base);
        }
        
        // Add overview snippet if available and not too long
        if (!empty($overview) && strlen($overview) < 200) {
            $base .= " " . truncate($overview, 100);
        }
        
        // Hard cap at 160 characters
        return truncate($base, 155);
    }
    
    /**
     * Extract year from media
     */
    private function extractYear(array $media): string {
        $dateField = isset($media['release_date']) ? 'release_date' : 'first_air_date';
        $date = $media[$dateField] ?? '';
        return empty($date) ? '2024' : substr($date, 0, 4);
    }
    
    /**
     * Extract genres as string or array
     */
    private function extractGenres(array $media): array {
        return array_map(fn($g) => $g['name'], $media['genres'] ?? []);
    }
}
