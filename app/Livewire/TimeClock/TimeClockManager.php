<?php

namespace App\Livewire\TimeClock;

use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\TimeSummary;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TimeClockManager extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterDate   = '';
    public string $filterDept   = '';
    public string $filterStatus = '';

    // Manual entry modal
    public bool   $showModal      = false;
    public int    $modalEmployeeId = 0;
    public string $modalDate      = '';
    public string $modalType      = 'clock_in';
    public string $modalNote      = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterDate'   => ['except' => ''],
        'filterDept'   => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function openManualEntry(int $employeeId = 0): void
    {
        $this->modalEmployeeId = $employeeId;
        $this->modalDate = now()->format('Y-m-d');
        $this->modalType = 'clock_in';
        $this->modalNote = '';
        $this->showModal = true;
    }

    public function saveManualEntry(): void
    {
        $this->validate([
            'modalEmployeeId' => 'required|integer|exists:employees,id',
            'modalType'       => 'required|in:clock_in,break_out,break_in,clock_out',
            'modalNote'       => 'nullable|string|max:255',
        ]);

        $now      = now();
        $employee = Employee::findOrFail($this->modalEmployeeId);

        TimeEntry::create([
            'tenant_id'   => $employee->tenant_id,
            'employee_id' => $this->modalEmployeeId,
            'date'        => $now->format('Y-m-d'),
            'type'        => $this->modalType,
            'time'        => $now->format('H:i'),
            'note'        => $this->modalNote ?: 'Registro de ponto',
            'ip_address'  => request()->ip(),
        ]);

        $this->modalDate = $now->format('Y-m-d');

        $this->recalculateSummary($this->modalEmployeeId, $this->modalDate);

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'Registro adicionado.');
    }

    public function deleteEntry(int $id): void
    {
        $entry = TimeEntry::findOrFail($id);
        $empId = $entry->employee_id;
        $date  = $entry->date->format('Y-m-d');
        $entry->delete();
        $this->recalculateSummary($empId, $date);
        $this->dispatch('toast', type: 'success', message: 'Registro removido.');
    }

    private function recalculateSummary(int $employeeId, string $date): void
    {
        $entries = TimeEntry::where('employee_id', $employeeId)
            ->where('date', $date)
            ->orderBy('time')
            ->get();

        if ($entries->isEmpty()) {
            TimeSummary::where('employee_id', $employeeId)->where('date', $date)->delete();
            return;
        }

        $employee = Employee::find($employeeId);
        $workedMinutes = 0;
        $breakMinutes  = 0;
        $clockIn = null;
        $breakOut = null;

        foreach ($entries as $entry) {
            $time = Carbon::parse($entry->time);
            match ($entry->type) {
                'clock_in'  => $clockIn = $time,
                'break_out' => (function () use ($time, &$clockIn, &$workedMinutes, &$breakOut) {
                    if ($clockIn) { $workedMinutes += $clockIn->diffInMinutes($time); $clockIn = null; }
                    $breakOut = $time;
                })(),
                'break_in'  => (function () use ($time, &$breakOut, &$breakMinutes, &$clockIn) {
                    if ($breakOut) { $breakMinutes += $breakOut->diffInMinutes($time); $breakOut = null; }
                    $clockIn = $time;
                })(),
                'clock_out' => (function () use ($time, &$clockIn, &$workedMinutes) {
                    if ($clockIn) { $workedMinutes += $clockIn->diffInMinutes($time); $clockIn = null; }
                })(),
            };
        }

        // Expected minutes based on shift
        $expectedMinutes = match ($employee->shift ?? 'I') {
            'I'     => 480, // 8h
            'II'    => 480,
            'III'   => 480,
            default => 480,
        };

        $overtime = max(0, $workedMinutes - $expectedMinutes);
        $deficit  = max(0, $expectedMinutes - $workedMinutes);

        $hasClockIn  = $entries->contains('type', 'clock_in');
        $hasClockOut = $entries->contains('type', 'clock_out');
        $status = ($hasClockIn && $hasClockOut) ? 'complete' : 'incomplete';

        TimeSummary::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $date],
            [
                'tenant_id'        => $employee->tenant_id,
                'worked_minutes'   => $workedMinutes,
                'expected_minutes' => $expectedMinutes,
                'overtime_minutes' => $overtime,
                'deficit_minutes'  => $deficit,
                'break_minutes'    => $breakMinutes,
                'status'           => $status,
            ]
        );
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        $date = $this->filterDate ?: now()->format('Y-m-d');

        $employees = Employee::with(['user', 'department', 'jobPosition'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->when($this->search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%")))
            ->orderBy('id')
            ->paginate(20);

        $employeeIds = $employees->pluck('id');

        $entries = TimeEntry::whereIn('employee_id', $employeeIds)
            ->where('date', $date)
            ->orderBy('time')
            ->get()
            ->groupBy('employee_id');

        $summaries = TimeSummary::whereIn('employee_id', $employeeIds)
            ->where('date', $date)
            ->get()
            ->keyBy('employee_id');

        // Apply status filter after resolving summaries
        if ($this->filterStatus) {
            $filteredIds = $summaries->where('status', $this->filterStatus)->keys();
            // Also include employees with no summary when filter = incomplete
            if ($this->filterStatus === 'incomplete') {
                $idsWithSummary = $summaries->keys();
                $idsWithoutSummary = $employeeIds->diff($idsWithSummary);
                $filteredIds = $filteredIds->merge($idsWithoutSummary);
            }
            $employees = $employees->filter(fn($emp) => $filteredIds->contains($emp->id));
        }

        $departments = \App\Models\Department::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('is_active', true)->orderBy('name')->get();

        $allEmployees = Employee::with('user')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->get();

        return view('livewire.time-clock.time-clock-manager', compact(
            'employees', 'entries', 'summaries', 'departments', 'date', 'allEmployees'
        ))->layout('layouts.app', ['title' => 'Ponto Eletrônico']);
    }
}
