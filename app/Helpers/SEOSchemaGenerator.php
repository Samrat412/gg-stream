<?php
/**
 * SEO Schema Generator
 * Generates JSON-LD structured data for movies and TV shows
 */

namespace App\Helpers;

class SEOSchemaGenerator {
    
    /**
     * Generate Movie schema
     */
    public function generateMovieSchema(array $movie, string $url): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Movie',
            'name' => $movie['title'] ?? '',
            'description' => truncate($movie['overview'] ?? '', 500),
            'image' => $this->getImageUrl($movie['poster_path']),
            'datePublished' => $movie['release_date'] ?? '',
            'duration' => isset($movie['runtime']) ? 'PT' . $movie['runtime'] . 'M' : '',
            'genre' => array_map(fn($g) => $g['name'], $movie['genres'] ?? []),
            'director' => $this->extractDirectors($movie),
            'actor' => $this->extractActors($movie),
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => round($movie['vote_average'] ?? 0, 1),
                'bestRating' => 10,
                'worstRating' => 1,
                'ratingCount' => $movie['vote_count'] ?? 0
            ],
            'potentialAction' => [
                '@type' => 'WatchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $url,
                    'actionPlatform' => [
                        'http://schema.org/DevicePlatform' => 'Web'
                    ]
                ]
            ]
        ];
        
        return array_filter($schema);
    }
    
    /**
     * Generate TV Series schema
     */
    public function generateTVSeriesSchema(array $show, string $url): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'TVSeries',
            'name' => $show['name'] ?? '',
            'description' => truncate($show['overview'] ?? '', 500),
            'image' => $this->getImageUrl($show['poster_path']),
            'datePublished' => $show['first_air_date'] ?? '',
            'endDate' => $show['status'] === 'Ended' ? ($show['last_air_date'] ?? '') : '',
            'numberOfSeasons' => $show['number_of_seasons'] ?? 0,
            'numberOfEpisodes' => $show['number_of_episodes'] ?? 0,
            'genre' => array_map(fn($g) => $g['name'], $show['genres'] ?? []),
            'director' => $this->extractCreators($show),
            'actor' => $this->extractActors($show),
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => round($show['vote_average'] ?? 0, 1),
                'bestRating' => 10,
                'worstRating' => 1,
                'ratingCount' => $show['vote_count'] ?? 0
            ],
            'potentialAction' => [
                '@type' => 'WatchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $url,
                    'actionPlatform' => [
                        'http://schema.org/DevicePlatform' => 'Web'
                    ]
                ]
            ]
        ];
        
        return array_filter($schema);
    }
    
    /**
     * Generate VideoObject schema
     */
    public function generateVideoObjectSchema(array $media, bool $isTV): array {
        $title = $media['title'] ?? $media['name'] ?? '';
        $thumbnail = $this->getImageUrl($media['backdrop_path'] ?? $media['poster_path']);
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $title,
            'description' => truncate($media['overview'] ?? '', 500),
            'thumbnailUrl' => $thumbnail,
            'uploadDate' => $media['release_date'] ?? $media['first_air_date'] ?? '',
            'contentUrl' => SITE_DOMAIN . '/watch/' . ($isTV ? 'tv' : 'movie') . '/' . ($media['id'] ?? ''),
            'embedUrl' => SITE_DOMAIN . '/watch/' . ($isTV ? 'tv' : 'movie') . '/' . ($media['id'] ?? ''),
            'duration' => isset($media['runtime']) ? 'PT' . $media['runtime'] . 'M' : '',
            'interactionCount' => $media['vote_count'] ?? 0
        ];
    }
    
    /**
     * Generate BreadcrumbList schema
     */
    public function generateBreadcrumbSchema(array $breadcrumbs): array {
        $items = [];
        foreach ($breadcrumbs as $index => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url']
            ];
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
    }
    
    /**
     * Generate WebPage schema
     */
    public function generateWebPageSchema(string $title, string $description, string $url): array {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $title,
            'description' => $description,
            'url' => $url,
            'publisher' => [
                '@type' => 'Organization',
                'name' => SITE_NAME,
                'url' => SITE_DOMAIN
            ]
        ];
    }
    
    /**
     * Generate FAQ schema
     */
    public function generateFAQSchema(array $media, bool $isTV): array {
        $title = $media['title'] ?? $media['name'] ?? '';
        $year = substr($media['release_date'] ?? $media['first_air_date'] ?? '', 0, 4);
        
        $questions = [
            [
                'question' => "Where can I watch {$title} for free?",
                'answer' => "You can watch {$title} ({$year}) free online on our streaming platform. No signup or registration required. Stream in HD quality instantly."
            ],
            [
                'question' => "Is {$title} available to stream online?",
                'answer' => "Yes, {$title} is available to stream online for free. We offer HD streaming with no ads or interruptions."
            ],
            [
                'question' => "What is {$title} about?",
                'answer' => truncate($media['overview'] ?? "{$title} is a popular " . ($isTV ? 'TV series' : 'movie') . ".", 200)
            ],
            [
                'question' => "Who stars in {$title}?",
                'answer' => $this->getCastDescription($media)
            ],
            [
                'question' => "Do I need to sign up to watch {$title}?",
                'answer' => "No, you don't need to sign up or create an account to watch {$title}. Our platform offers free streaming without any registration requirements."
            ]
        ];
        
        if ($isTV) {
            $questions[] = [
                'question' => "How many seasons does {$title} have?",
                'answer' => "{$title} has " . ($media['number_of_seasons'] ?? 'multiple') . " seasons available to stream."
            ];
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn($q) => [
                '@type' => 'Question',
                'name' => $q['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $q['answer']
                ]
            ], $questions)
        ];
    }
    
    /**
     * Combine multiple schemas into @graph
     */
    public function combineSchemas(array ...$schemas): array {
        return ['@graph' => $schemas];
    }
    
    /**
     * Helper: Get image URL
     */
    private function getImageUrl(?string $path): string {
        if (!$path) return '';
        return 'https://image.tmdb.org/t/p/original' . $path;
    }
    
    /**
     * Helper: Extract directors from credits
     */
    private function extractDirectors(array $movie): array {
        $credits = $movie['credits'] ?? [];
        $crew = $credits['crew'] ?? [];
        $directors = array_filter($crew, fn($p) => $p['job'] === 'Director');
        return array_map(fn($p) => ['@type' => 'Person', 'name' => $p['name']], $directors);
    }
    
    /**
     * Helper: Extract creators from credits
     */
    private function extractCreators(array $show): array {
        $createdBy = $show['created_by'] ?? [];
        return array_map(fn($c) => ['@type' => 'Person', 'name' => $c['name']], $createdBy);
    }
    
    /**
     * Helper: Extract actors from credits
     */
    private function extractActors(array $media): array {
        $credits = $media['credits'] ?? [];
        $cast = $credits['cast'] ?? [];
        $topCast = array_slice($cast, 0, 5);
        return array_map(fn($p) => ['@type' => 'Person', 'name' => $p['name']], $topCast);
    }
    
    /**
     * Helper: Get cast description
     */
    private function getCastDescription(array $media): string {
        $credits = $media['credits'] ?? [];
        $cast = $credits['cast'] ?? [];
        if (empty($cast)) return "Featuring a talented ensemble cast.";
        
        $names = array_slice(array_column($cast, 'name'), 0, 4);
        return "Starring " . implode(', ', $names) . ".";
    }
}
