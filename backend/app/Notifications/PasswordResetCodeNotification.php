<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PasswordResetCodeNotification extends Notification
{
    public function __construct(public readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Redefinir senha do DirectChannel')
            ->greeting('Olá!')
            ->line('Seu código é '.$this->code.', expira em '.config('auth.passwords.users.expire').' minutos.')
            ->line('Se você não solicitou a redefinição, ignore este e-mail.')
            ->salutation('Equipe DirectChannel');
    }
}
