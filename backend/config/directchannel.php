<?php

declare(strict_types=1);

return [
    'max_upload_mb' => (int) env('DC_MAX_UPLOAD_MB', 25),
    'log_retention_days' => (int) env('DC_LOG_RETENTION_DAYS', 30),
];
