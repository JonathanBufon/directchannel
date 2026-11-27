<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait UsesMicrosecondTimestamps
{
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s.uP';
    }
}
