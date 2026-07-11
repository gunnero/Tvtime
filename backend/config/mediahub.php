<?php

return [
    'version' => env('MEDIAHUB_VERSION', '1.0.0'),
    'web_player_enabled' => (bool) env('MEDIAHUB_WEB_PLAYER_ENABLED', false),
    'web_providers_enabled' => (bool) env('MEDIAHUB_WEB_PROVIDERS_ENABLED', false),
];
