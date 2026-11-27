<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportStatus: string
{
    case Received = 'recebida';
    case Concluded = 'concluida';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'recebida',
            self::Concluded => 'concluída',
        };
    }
}
