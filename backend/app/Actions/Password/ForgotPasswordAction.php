<?php

declare(strict_types=1);

namespace App\Actions\Password;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Password;

final class ForgotPasswordAction
{
    public function execute(string $email): void
    {
        // T22 accepts the broker's opaque token directly; no web reset route is required.
        ResetPassword::toMailUsing(fn (User $user, string $token): MailMessage => (new MailMessage)
            ->subject('Redefinir senha do DirectChannel')
            ->greeting('Olá, '.$user->name.'!')
            ->line('Recebemos uma solicitação para redefinir sua senha.')
            ->line('Informe este código na tela de redefinição de senha do aplicativo:')
            ->line($token)
            ->line('O código expira em '.config('auth.passwords.users.expire').' minutos.')
            ->line('Se você não solicitou a redefinição, ignore este e-mail.')
            ->salutation('Equipe DirectChannel'));

        // All broker outcomes, including an unknown user or its own throttle, are private.
        Password::sendResetLink(['email' => $email]);
    }
}
