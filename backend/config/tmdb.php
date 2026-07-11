<?php

return [
    'enabled' => env('TMDB_ENABLED', false),
    'api_key' => env('TMDB_API_KEY'),
    'timeout' => (int) env('TMDB_TIMEOUT', 20),
    'cache_ttl' => (int) env('TMDB_CACHE_TTL', 86400),
    'cache_store' => env('TMDB_CACHE_STORE', 'file'),
    'base_url' => 'https://api.themoviedb.org/3',
    'image_base_url' => 'https://image.tmdb.org/t/p',
];
