<?php

declare(strict_types=1);

namespace App\Http\Requests\Groups;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

abstract class GroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('group') !== null) {
            Gate::authorize($this->route()->getActionMethod() === 'leave' ? 'view' : 'manage', $this->route('group'));
        }

        return true;
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto.',
            'array' => 'O campo :attribute deve ser uma lista.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ter no mínimo :min.',
            'max' => 'O campo :attribute deve ter no máximo :max.',
            'distinct' => 'Não repita participantes na lista.',
            'not_in' => 'Você já participa do grupo e não deve constar nesta lista.',
        ];
    }

    protected function memberRules(): array
    {
        return [
            'member_ids' => ['required', 'array', 'min:1', 'max:255'],
            'member_ids.*' => ['required', 'integer', 'min:1', 'distinct', 'not_in:'.$this->user()->id],
        ];
    }
}
