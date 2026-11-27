<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

abstract class AuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto.',
            'email' => 'Informe um e-mail válido.',
            'min' => 'O campo :attribute deve ter pelo menos :min caracteres.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
            'confirmed' => 'A confirmação da senha não confere.',
            'unique' => 'Este :attribute já está em uso.',
            'regex' => 'Use apenas letras, números, ponto e sublinhado no nome de usuário.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nome', 'email' => 'e-mail', 'username' => 'nome de usuário', 'password' => 'senha', 'device_name' => 'nome do dispositivo'];
    }
}
