<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Http\Requests\Auth\AuthRequest;

abstract class ApiRequest extends AuthRequest
{
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
            'exists' => 'O usuário informado não está disponível.',
            'not_in' => 'Você não pode selecionar sua própria conta.',
            'current_password' => 'A senha atual está incorreta.',
            'required_with' => 'O campo :attribute é obrigatório.',
            'required_without' => 'Informe pelo menos uma preferência.',
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'current_password' => 'senha atual', 'search' => 'busca', 'user_id' => 'usuário',
            'public_payload' => 'chave pública', 'token' => 'código de recuperação',
            'read_receipts_enabled' => 'confirmação de leitura',
            'notification_preview_enabled' => 'prévia de notificações',
        ]);
    }
}
