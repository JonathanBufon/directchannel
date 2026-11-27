<?php

declare(strict_types=1);

namespace App\Http\Requests\Messages;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

abstract class MessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('viewAny', [Message::class, $this->route('conversation')]);

        return true;
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'string' => 'O campo :attribute deve ser um texto.',
            'array' => 'O campo :attribute deve ser uma lista.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute deve ter no máximo :max.',
            'distinct' => 'Não repita uploads na mesma mensagem.',
        ];
    }
}
