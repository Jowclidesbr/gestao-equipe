<?php

namespace App\Notifications;

use App\Models\VacationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly VacationRequest $vacationRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('❌ Solicitação de férias não aprovada')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Infelizmente sua solicitação de férias foi **rejeitada**.")
            ->line("**Período solicitado:** {$this->vacationRequest->start_date->format('d/m/Y')} a {$this->vacationRequest->end_date->format('d/m/Y')}")
            ->line("**Motivo:** {$this->vacationRequest->approver_notes}")
            ->action('Fazer nova solicitação', route('employee.vacation.create'))
            ->line('Entre em contato com seu gestor para agendar um novo período.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'vacation_rejected',
            'vacation_request_id'=> $this->vacationRequest->id,
            'approver_notes'     => $this->vacationRequest->approver_notes,
        ];
    }
}
