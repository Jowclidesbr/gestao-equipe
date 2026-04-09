<?php

namespace App\Notifications;

use App\Models\VacationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VacationRequestApprovedNotification extends Notification
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
            ->subject('✅ Suas férias foram aprovadas!')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Suas férias foram **aprovadas**.")
            ->line("**Período:** {$this->vacationRequest->start_date->format('d/m/Y')} a {$this->vacationRequest->end_date->format('d/m/Y')}")
            ->line("**Dias:** {$this->vacationRequest->days_requested}")
            ->when($this->vacationRequest->approver_notes, fn($m) =>
                $m->line("**Observação do gestor:** {$this->vacationRequest->approver_notes}")
            )
            ->action('Ver detalhes', route('employee.vacation.index'))
            ->line('Boas férias! 🏖');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'vacation_approved',
            'vacation_request_id'=> $this->vacationRequest->id,
            'start_date'         => $this->vacationRequest->start_date->toDateString(),
            'end_date'           => $this->vacationRequest->end_date->toDateString(),
        ];
    }
}
