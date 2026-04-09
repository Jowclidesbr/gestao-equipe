<?php

namespace App\Livewire\Vacation;

use App\Services\VacationApprovalService;
use Livewire\Component;

class VacationRequestForm extends Component
{
    public string $start_date   = '';
    public string $end_date     = '';
    public int    $sell_days    = 0;
    public string $notes        = '';

    // Computed / display only
    public int $balance = 0;
    public int $daysRequested = 0;

    public function mount(): void
    {
        $this->balance = auth()->user()->employee?->vacation_balance_days ?? 0;
    }

    public function updatedStartDate(): void
    {
        $this->computeDays();
    }

    public function updatedEndDate(): void
    {
        $this->computeDays();
    }

    private function computeDays(): void
    {
        if ($this->start_date && $this->end_date) {
            try {
                $start = \Carbon\Carbon::parse($this->start_date);
                $end   = \Carbon\Carbon::parse($this->end_date);
                $this->daysRequested = max(0, (int) $start->diffInDays($end) + 1);
            } catch (\Exception) {
                $this->daysRequested = 0;
            }
        }
    }

    public function submit(VacationApprovalService $service): void
    {
        $this->validate([
            'start_date' => 'required|date|after:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'sell_days'  => 'integer|min:0|max:10',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $employee = auth()->user()->employee;

        if (! $employee) {
            $this->addError('start_date', 'Seu perfil de colaborador não foi encontrado.');
            return;
        }

        try {
            $service->submit($employee, [
                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,
                'sell_days'  => $this->sell_days,
                'notes'      => $this->notes,
            ]);

            $this->reset(['start_date', 'end_date', 'sell_days', 'notes', 'daysRequested']);
            $this->dispatch('toast', type: 'success', message: 'Solicitação de férias enviada! Aguarde aprovação do seu gestor.');
            $this->redirect(route('employee.vacation.index'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
        }
    }

    public function render()
    {
        return view('livewire.vacation.vacation-request-form')
            ->layout('layouts.app', ['title' => 'Solicitar Férias']);
    }
}
