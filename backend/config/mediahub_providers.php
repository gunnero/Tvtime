<?php

return [
    'timeout' => (int) env('MEDIAHUB_PROVIDER_TIMEOUT', 20),
    'max_response_bytes' => (int) env('MEDIAHUB_PROVIDER_MAX_RESPONSE_BYTES', 26214400),
    'sync_limit' => (int) env('MEDIAHUB_PROVIDER_SYNC_LIMIT', 5000),
    'series_detail_limit' => (int) env('MEDIAHUB_PROVIDER_SERIES_DETAIL_LIMIT', 100),
];
