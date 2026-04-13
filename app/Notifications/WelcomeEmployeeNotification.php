<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmployeeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $temporaryPassword
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bem-vindo ao ' . (config('app.name') . '!'))
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Sua conta foi criada com sucesso. Utilize as credenciais abaixo para acessar o sistema.')
            ->line("**E-mail:** {$notifiable->email}")
            ->line("**Senha temporária:** `{$this->temporaryPassword}`")
            ->action('Acessar o Sistema', url(config('app.url')))
            ->line('Por segurança, altere sua senha no primeiro acesso em **Meu Perfil**.')
            ->salutation('Atenciosamente, Equipe de RH');
    }
}
