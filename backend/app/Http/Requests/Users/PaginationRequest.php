<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

final class PaginationRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['page' => ['sometimes', 'integer', 'min:1']];
    }
}
