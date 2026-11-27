<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class AdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admin');
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'string' => 'O campo :attribute deve ser um texto.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute deve ter no máximo :max.',
            'in' => 'O valor informado para :attribute é inválido.',
            'enum' => 'O valor informado para :attribute é inválido.',
        ];
    }

    public function attributes(): array
    {
        return ['reason' => 'motivo', 'decision' => 'decisão', 'search' => 'busca', 'status' => 'estado', 'page' => 'página', 'max_upload_bytes' => 'tamanho máximo em bytes', 'log_retention_days' => 'retenção de logs em dias'];
    }
}
