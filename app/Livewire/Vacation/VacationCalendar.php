<?php

namespace App\Livewire\Vacation;

use App\Models\Department;
use App\Models\VacationRequest;
use Livewire\Component;

class VacationCalendar extends Component
{
    public string $filterDept   = '';
    public string $filterStatus = '';

    public function getEventsProperty(): array
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $requests = VacationRequest::with('employee.user', 'employee.department')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('status', $this->filterStatus ? [$this->filterStatus] : ['approved', 'pending'])
            ->when($this->filterDept, fn($q) => $q->whereHas('employee', fn($e) => $e->where('department_id', $this->filterDept)))
            ->get();

        return $requests->map(function (VacationRequest $vr) {
            $colors = match ($vr->status) {
                'approved'  => ['#16a34a', '#15803d'], // green
                'pending'   => ['#f59e0b', '#d97706'], // amber
                'rejected'  => ['#ef4444', '#dc2626'], // red
                'cancelled' => ['#9ca3af', '#6b7280'], // gray
                default     => ['#6366f1', '#4f46e5'],
            };

            return [
                'id'              => $vr->id,
                'title'           => $vr->employee->user->name,
                'start'           => $vr->start_date->format('Y-m-d'),
                'end'             => $vr->end_date->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                'backgroundColor' => $colors[0],
                'borderColor'     => $colors[1],
                'extendedProps'   => [
                    'department' => $vr->employee->department->name ?? '—',
                    'days'       => $vr->days_requested,
                    'status'     => $vr->status_label,
                    'notes'      => $vr->employee_notes ?? '',
                ],
            ];
        })->toArray();
    }

    public function render()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $departments = Department::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('is_active', true)->orderBy('name')->get();

        return view('livewire.vacation.vacation-calendar', [
            'departments' => $departments,
            'events'      => $this->events,
        ])->layout('layouts.app', ['title' => 'Calendário de Férias']);
    }
}
