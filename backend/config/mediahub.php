<?php

return [
    'version' => env('MEDIAHUB_VERSION', '1.0.0'),
    'web_player_enabled' => (bool) env('MEDIAHUB_WEB_PLAYER_ENABLED', false),
    'web_providers_enabled' => (bool) env('MEDIAHUB_WEB_PROVIDERS_ENABLED', false),
    'monitoring' => [
        'enabled' => (bool) env('MEDIAHUB_MONITORING_ENABLED', true),
        'slow_request_ms' => (int) env('MEDIAHUB_SLOW_REQUEST_MS', 1000),
        'log_glob' => storage_path('logs/mediahub-monitoring*.log'),
    ],
];
