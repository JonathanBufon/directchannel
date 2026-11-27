<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

final class ListOwnReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['page' => ['sometimes', 'integer', 'min:1']];
    }

    public function messages(): array
    {
        return ['integer' => 'A página deve ser um número inteiro.', 'min' => 'A página deve ser no mínimo 1.'];
    }
}
