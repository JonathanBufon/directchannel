<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportReason: string
{
    case Harassment = 'Assédio ou contato indesejado';
    case Spam = 'Spam ou mensagens repetitivas';
    case Fraud = 'Golpe, fraude ou conduta suspeita';
}
