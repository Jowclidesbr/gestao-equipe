<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\VacationRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * DashboardMetricsService
 *
 * Aggregates all HR metrics used by the Admin/Manager dashboard.
 * Uses raw queries where optimal for performance.
 */
class DashboardMetricsService
{
    public function getAdminMetrics(?int $tenantId): array
    {
        return [
            'headcount'           => $this->headcount($tenantId),
            'active_count'        => $this->activeCount($tenantId),
            'inactive_count'      => $this->inactiveCount($tenantId),
            'turnover_rate'       => $this->turnoverRate($tenantId),
            'pending_vacations'   => $this->pendingVacations($tenantId),
            'birthdays_this_month'=> $this->birthdaysThisMonth($tenantId),
            'upcoming_absences'   => $this->upcomingAbsences($tenantId),
            'upcoming_vacations'  => $this->upcomingVacations($tenantId),
            'open_positions'      => $this->openPositions($tenantId),
        ];
    }

    public function getEmployeeMetrics(int $employeeId): array
    {
        $employee = Employee::findOrFail($employeeId);
        return [
            'balance'          => $employee->vacation_balance_days,
            'concession_end'   => $employee->vacation_concession_end,
            'pending_requests' => VacationRequest::where('employee_id', $employeeId)
                                    ->where('status', 'pending')->count(),
            'approved_requests'=> VacationRequest::where('employee_id', $employeeId)
                                    ->where('status', 'approved')
                                    ->whereYear('start_date', now()->year)
                                    ->count(),
            'recent_requests'  => VacationRequest::where('employee_id', $employeeId)
                                    ->orderByDesc('submitted_at')
                                    ->limit(5)
                                    ->get(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PRIVATE METRIC METHODS
    // ─────────────────────────────────────────────────────────────────────────

    private function headcount(?int $tenantId): int
    {
        $q = Employee::query();
        if ($tenantId) $q->where('tenant_id', $tenantId);
        return $q->count();
    }

    private function activeCount(?int $tenantId): int
    {
        $q = Employee::where('status', 'active');
        if ($tenantId) $q->where('tenant_id', $tenantId);
        return $q->count();
    }

    private function inactiveCount(?int $tenantId): int
    {
        $q = Employee::whereIn('status', ['inactive', 'terminated']);
        if ($tenantId) $q->where('tenant_id', $tenantId);
        return $q->count();
    }

    /**
     * Turnover rate for the last 12 months (annualised).
     * Formula: (Terminations ÷ Average Headcount) × 100
     */
    private function turnoverRate(?int $tenantId): float
    {
        $since = now()->subMonths(12)->startOfDay();

        $q1 = Employee::where('status', 'terminated')->whereNotNull('dismissal_date')->where('dismissal_date', '>=', $since);
        if ($tenantId) $q1->where('tenant_id', $tenantId);
        $terminations = $q1->count();

        $q2 = Employee::where('admission_date', '<=', now());
        if ($tenantId) $q2->where('tenant_id', $tenantId);
        $avgHeadcount = max(1, $q2->count());

        return round(($terminations / $avgHeadcount) * 100, 1);
    }

    private function pendingVacations(?int $tenantId): int
    {
        $q = VacationRequest::where('status', 'pending');
        if ($tenantId) $q->where('tenant_id', $tenantId);
        return $q->count();
    }

    private function birthdaysThisMonth(?int $tenantId): Collection
    {
        $month = Carbon::now()->month;
        $q = Employee::where('status', 'active')->whereMonth('birth_date', $month);
        if ($tenantId) $q->where('tenant_id', $tenantId);
        return $q->with('user:id,name,avatar_path')->orderByRaw('DAY(birth_date) ASC')->get(['id', 'user_id', 'birth_date', 'department_id']);
    }

    private function upcomingAbsences(?int $tenantId): Collection
    {
        $q = DB::table('absences')
            ->join('employees', 'employees.id', '=', 'absences.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->whereNull('absences.deleted_at')
            ->whereBetween('absences.start_date', [now()->toDateString(), now()->addDays(14)->toDateString()]);
        if ($tenantId) $q->where('absences.tenant_id', $tenantId);
        return $q->select('users.name', 'absences.type', 'absences.start_date', 'absences.end_date')
            ->orderBy('absences.start_date')->limit(10)->get();
    }

    private function upcomingVacations(?int $tenantId): Collection
    {
        $q = VacationRequest::where('status', 'approved')
            ->where('start_date', '>=', now()->toDateString())
            ->where('start_date', '<=', now()->addDays(30)->toDateString());
        if ($tenantId) $q->where('tenant_id', $tenantId);
        return $q->with('employee.user:id,name')->orderBy('start_date')->limit(10)->get();
    }

    private function openPositions(?int $tenantId): int
    {
        $q = DB::table('job_openings')->where('status', 'open')->whereNull('deleted_at');
        if ($tenantId) $q->where('tenant_id', $tenantId);
        return (int) $q->sum('vacancies');
    }
}
