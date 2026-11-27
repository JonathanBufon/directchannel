<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ReportStatus;
use Illuminate\Validation\Rule;

final class ListReportsRequest extends AdminRequest
{
    public function rules(): array
    {
        return ['status' => ['nullable', Rule::enum(ReportStatus::class)], 'page' => ['sometimes', 'integer', 'min:1']];
    }
}
