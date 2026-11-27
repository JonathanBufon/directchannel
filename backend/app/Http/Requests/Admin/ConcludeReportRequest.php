<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

final class ConcludeReportRequest extends AdminRequest
{
    public function rules(): array
    {
        return ['decision' => ['required', 'string', 'max:2000']];
    }
}
