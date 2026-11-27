<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AppSetting;

final class AppSettings
{
    public function maxUploadBytes(): int
    {
        return $this->positiveInteger('max_upload_bytes', 25 * 1024 * 1024);
    }

    public function logRetentionDays(): int
    {
        return $this->positiveInteger('log_retention_days', 30);
    }

    public function all(): array
    {
        return ['max_upload_bytes' => $this->maxUploadBytes(), 'log_retention_days' => $this->logRetentionDays()];
    }

    private function positiveInteger(string $key, int $default): int
    {
        $value = AppSetting::find($key)?->value;

        return is_int($value) && $value > 0 ? $value : $default;
    }
}
