<?php

namespace App\Notifications;

use App\Models\VacationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestSubmittedNotification extends Notification
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
            ->subject('Nova Solicitação de Férias — ' . $this->vacationRequest->employee->user->name)
            ->greeting("Olá, {$notifiable->name}!")
            ->line("**{$this->vacationRequest->employee->user->name}** solicitou férias:")
            ->line("**Período:** {$this->vacationRequest->start_date->format('d/m/Y')} a {$this->vacationRequest->end_date->format('d/m/Y')}")
            ->line("**Dias:** {$this->vacationRequest->days_requested}")
            ->action('Revisar Solicitação', route('admin.vacations.index'))
            ->line('Por favor, aprove ou rejeite a solicitação em até 5 dias úteis.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'vacation_submitted',
            'vacation_request_id'=> $this->vacationRequest->id,
            'employee_name'      => $this->vacationRequest->employee->user->name,
            'start_date'         => $this->vacationRequest->start_date->toDateString(),
            'end_date'           => $this->vacationRequest->end_date->toDateString(),
        ];
    }
}
