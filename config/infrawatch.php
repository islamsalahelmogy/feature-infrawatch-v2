<?php

return [
    'worker_token' => env('INFRAWATCH_WORKER_TOKEN'),
    'worker_stale_after_seconds' => (int) env('INFRAWATCH_WORKER_STALE_AFTER_SECONDS', 120),
];
