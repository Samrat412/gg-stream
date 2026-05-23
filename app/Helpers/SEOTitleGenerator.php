<?php
/**
 * SEO Title Generator
 * Generates unique, SEO-optimized titles for movies and TV shows
 */

namespace App\Helpers;

class SEOTitleGenerator {
    private const TITLE_TEMPLATES = [
        // Movie templates
        'Watch {title} ({year}) Free Online - {quality} Streaming No Signup',
        '{title} ({year}) Full Movie Free - Stream in {quality} HD',
        'Stream {title} Free Online - Watch Full Movie in HD No Registration',
        '{title} - Watch Free Movie Online ({year}) {quality}',
        'Free Streaming: {title} ({year}) - Full HD No Signup Required',
        'Watch {title} Online Free - {quality} Movie Stream No Ads',
        '{title} ({year}) - Free Full Movie Streaming in HD',
        'Stream Free: {title} - Watch Online Without Signing Up',
        '{title} Full Movie - Watch Free Online ({year}) {quality} Stream',
        'Watch Online: {title} ({year}) Free Movie No Registration',
        
        // TV templates
        'Watch {title} Free Online - Stream Full Series in HD',
        '{title} - Free TV Series Streaming No Signup ({year})',
        'Stream {title} Full Episodes Free - Watch Online HD',
        '{title} ({year}) - Watch Free TV Show Online All Seasons',
        'Free Streaming: {title} TV Series - All Episodes in HD',
        'Watch {title} Online Free - Full TV Series No Registration',
        '{title} - Stream Free TV Show Episodes in {quality}',
        'Stream Full Series: {title} - Watch Free Online No Ads',
        '{title} ({year}) Free - Watch Complete TV Series Online',
        'Watch Free: {title} TV Show - Stream All Episodes HD'
    ];
    
    /**
     * Generate unique title based on media
     */
    public function generateUniqueTitle(array $media, bool $isTV, ?int $season = null, ?int $episode = null): string {
        $title = $media['title'] ?? $media['name'] ?? '';
        $year = $this->extractYear($media);
        $quality = detectQuality($media);
        
        // Select template deterministically based on hash
        $hash = hashCode($title . ($isTV ? '_tv' : '_movie'));
        $templateIndex = abs($hash) % count(self::TITLE_TEMPLATES);
        
        // Filter templates - use TV templates for TV shows
        $templates = $isTV 
            ? array_slice(self::TITLE_TEMPLATES, 10) 
            : array_slice(self::TITLE_TEMPLATES, 0, 10);
        
        $templateIndex = abs($hash) % count($templates);
        $template = $templates[$templateIndex];
        
        // Replace placeholders
        $result = str_replace(
            ['{title}', '{year}', '{quality}'],
            [$title, $year, $quality],
            $template
        );
        
        // Add season/episode info for TV
        if ($isTV && $season !== null) {
            if ($episode !== null) {
                $result .= " - Season {$season} Episode {$episode}";
            } else {
                $result .= " - Season {$season}";
            }
        }
        
        return $result;
    }
    
    /**
     * Extract year from media
     */
    private function extractYear(array $media): string {
        $dateField = isset($media['release_date']) ? 'release_date' : 'first_air_date';
        $date = $media[$dateField] ?? '';
        
        if (empty($date)) {
            return '2024';
        }
        
        return substr($date, 0, 4);
    }
    
    /**
     * Simple hash function for deterministic selection
     */
    private function hashCode(string $str): int {
        return \hashCode($str);
    }
}
