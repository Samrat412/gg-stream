<?php
/**
 * SEO Authority Data
 * Entity links and editorial topics
 */

return [
    // Entity Links for building topical authority
    'ENTITY_LINKS' => [
        'actors' => [
            'tom-cruise', 'leonardo-dicaprio', 'scarlett-johansson',
            'denzel-washington', 'margot-robbie', 'keanu-reeves'
        ],
        'directors' => [
            'christopher-nolan', 'steven-spielberg', 'james-cameron',
            'martin-scorsese', 'denis-villeneuve', 'greta-gerwig'
        ],
        'studios' => [
            'marvel-studios', 'warner-bros-pictures', 'universal-pictures',
            'paramount-pictures', 'walt-disney-pictures'
        ],
        'collections' => [
            'harry-potter', 'lord-of-the-rings', 'fast-and-furious',
            'mission-impossible', 'john-wick'
        ]
    ],
    
    // Editorial Topics
    'EDITORIAL_TOPICS' => [
        'best-movies-2026' => [
            'title' => 'Best Movies of 2026',
            'h1' => 'Best Movies of 2026 - Watch Free Online',
            'endpoint' => '/discover/movie',
            'params' => ['primary_release_year' => '2026', 'sort_by' => 'popularity.desc'],
            'sections' => ['trending', 'top-rated', 'new-releases']
        ],
        'action-movies' => [
            'title' => 'Best Action Movies',
            'h1' => 'Best Action Movies - Watch Free Online',
            'endpoint' => '/discover/movie',
            'params' => ['with_genres' => '28', 'sort_by' => 'popularity.desc'],
            'sections' => ['popular', 'top-rated', 'classics']
        ],
        'anime-series' => [
            'title' => 'Best Anime Series',
            'h1' => 'Best Anime Series - Watch Free Online',
            'endpoint' => '/discover/tv',
            'params' => ['with_genres' => '16', 'with_origin_country' => 'JP', 'sort_by' => 'popularity.desc'],
            'sections' => ['popular', 'top-rated', 'new-seasons']
        ],
        'sci-fi-movies' => [
            'title' => 'Best Sci-Fi Movies',
            'h1' => 'Best Science Fiction Movies - Watch Free Online',
            'endpoint' => '/discover/movie',
            'params' => ['with_genres' => '878', 'sort_by' => 'popularity.desc'],
            'sections' => ['popular', 'classics', 'modern']
        ]
    ],
    
    // Best topic slugs
    'BEST_TOPIC_SLUGS' => ['best-movies-2026', 'anime-series', 'sci-fi-movies'],
    
    // Top topic slugs
    'TOP_TOPIC_SLUGS' => ['action-movies'],
    
    // Movies-like slugs
    'MOVIES_LIKE_SLUGS' => ['inception', 'interstellar', 'john-wick', 'the-dark-knight']
];
