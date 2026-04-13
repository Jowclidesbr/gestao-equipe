<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\TimeSummary;
use App\Models\VacationRequest;
use Carbon\Carbon;
use Livewire\Component;

class Reports extends Component
{
    public string $period = 'month'; // month, quarter, year
    public string $filterDept = '';

    public function render()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $now  = Carbon::now();
        $from = match ($this->period) {
            'quarter' => $now->copy()->subMonths(3)->startOfMonth(),
            'year'    => $now->copy()->subYear()->startOfMonth(),
            default   => $now->copy()->startOfMonth(),
        };

        $employeesQuery = Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept));

        // ── Headcount ──
        $totalActive    = (clone $employeesQuery)->where('status', 'active')->count();
        $totalInactive  = (clone $employeesQuery)->whereIn('status', ['inactive', 'terminated'])->count();
        $totalOnLeave   = (clone $employeesQuery)->where('status', 'on_leave')->count();
        $headcount      = $totalActive + $totalOnLeave;

        // ── Headcount by department ──
        $byDepartment = Employee::selectRaw('department_id, count(*) as total')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->groupBy('department_id')
            ->with('department')
            ->get()
            ->map(fn($row) => [
                'name'  => $row->department->name ?? 'Sem Departamento',
                'total' => $row->total,
            ])->sortByDesc('total')->values();

        // ── By shift ──
        $byShift = Employee::selectRaw("COALESCE(shift, 'Não definido') as shift_label, count(*) as total")
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->groupBy('shift_label')
            ->pluck('total', 'shift_label');

        // ── By team ──
        $byTeam = Employee::selectRaw("COALESCE(team, 'Não definido') as team_label, count(*) as total")
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->groupBy('team_label')
            ->pluck('total', 'team_label');

        // ── By contract type ──
        $byContract = Employee::selectRaw('contract_type, count(*) as total')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->groupBy('contract_type')
            ->pluck('total', 'contract_type');

        // ── Turnover ──
        $admissions  = Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('admission_date', [$from, $now])
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->count();

        $dismissals  = Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('dismissal_date', [$from, $now])
            ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
            ->count();

        $turnoverRate = $headcount > 0
            ? round((($admissions + $dismissals) / 2) / $headcount * 100, 1)
            : 0;

        // ── Vacations pending ──
        $vacationsPending  = VacationRequest::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'pending')->count();
        $vacationsApproved = VacationRequest::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'approved')
            ->whereBetween('start_date', [$from, $now->endOfMonth()])
            ->count();

        // ── Overtime summary (current month) ──
        $overtimeData = TimeSummary::selectRaw('SUM(overtime_minutes) as total_overtime, SUM(deficit_minutes) as total_deficit, SUM(worked_minutes) as total_worked')
            ->when($tenantId, fn($q) => $q->whereHas('employee', fn($sub) => $sub->where('tenant_id', $tenantId)))
            ->whereBetween('date', [$from, $now])
            ->first();

        $totalOvertime = $overtimeData->total_overtime ?? 0;
        $totalDeficit  = $overtimeData->total_deficit ?? 0;

        // ── Monthly admissions trend (last 6 months) ──
        $admissionTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $count = Employee::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereYear('admission_date', $month->year)
                ->whereMonth('admission_date', $month->month)
                ->when($this->filterDept, fn($q) => $q->where('department_id', $this->filterDept))
                ->count();
            $admissionTrend->push(['month' => $month->isoFormat('MMM/YY'), 'count' => $count]);
        }

        $departments = Department::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.reports', compact(
            'headcount', 'totalActive', 'totalInactive', 'totalOnLeave',
            'byDepartment', 'byShift', 'byTeam', 'byContract',
            'turnoverRate', 'admissions', 'dismissals',
            'vacationsPending', 'vacationsApproved',
            'totalOvertime', 'totalDeficit',
            'admissionTrend', 'departments'
        ))->layout('layouts.app', ['title' => 'Relatórios']);
    }
}
