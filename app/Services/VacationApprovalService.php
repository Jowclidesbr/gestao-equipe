<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\VacationRequest;
use App\Notifications\VacationRequestApprovedNotification;
use App\Notifications\VacationRequestRejectedNotification;
use App\Notifications\VacationRequestSubmittedNotification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * VacationApprovalService
 *
 * Encapsulates all business logic for vacation requests:
 *   - Validation of balance & legal rules (CLT)
 *   - Overlap detection
 *   - Approval / Rejection workflow
 *   - Balance deduction / restoration
 */
class VacationApprovalService
{
    // ── CLT Constants ──────────────────────────────────────────────────────────
    /** Minimum vacation period per CLT (can be split into max 3 periods) */
    const MIN_DAYS_PER_PERIOD  = 14;
    /** Maximum days that can be sold as cash (abono pecuniário) */
    const MAX_SELL_DAYS        = 10;
    /** Minimum days in advance to request vacation */
    const MIN_ADVANCE_DAYS     = 30;

    // ─────────────────────────────────────────────────────────────────────────
    //  PUBLIC API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Submit a new vacation request from an employee.
     *
     * @throws ValidationException
     */
    public function submit(Employee $employee, array $data): VacationRequest
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate   = Carbon::parse($data['end_date'])->endOfDay();

        $daysRequested = $this->countWorkingDays($startDate, $endDate);
        $sellDays      = (int) ($data['sell_days'] ?? 0);

        $this->validateSubmission($employee, $startDate, $endDate, $daysRequested, $sellDays);

        return DB::transaction(function () use ($employee, $startDate, $endDate, $daysRequested, $sellDays, $data) {
            $request = VacationRequest::create([
                'tenant_id'        => $employee->tenant_id,
                'employee_id'      => $employee->id,
                'start_date'       => $startDate,
                'end_date'         => $endDate,
                'days_requested'   => $daysRequested,
                'sell_days'        => $sellDays,
                'status'           => VacationRequest::STATUS_PENDING,
                'employee_notes'   => $data['notes'] ?? null,
                'submitted_at'     => now(),
                'balance_snapshot' => $employee->vacation_balance_days,
            ]);

            // Notify the employee's manager
            $manager = $employee->manager?->user;
            $manager?->notify(new VacationRequestSubmittedNotification($request));

            return $request;
        });
    }

    /**
     * Approve a pending vacation request.
     *
     * @throws ValidationException
     */
    public function approve(VacationRequest $request, User $approver, ?string $notes = null): VacationRequest
    {
        $this->ensureCanBeReviewed($request);

        $employee = $request->employee;
        $totalDeduction = $request->days_requested + $request->sell_days;

        // Re-validate balance at approval time (balance may have changed)
        if ($employee->vacation_balance_days < $totalDeduction) {
            throw ValidationException::withMessages([
                'balance' => "Saldo insuficiente. Disponível: {$employee->vacation_balance_days} dias. Requerido: {$totalDeduction} dias.",
            ]);
        }

        return DB::transaction(function () use ($request, $approver, $notes, $employee, $totalDeduction) {
            // Deduct balance
            $employee->decrement('vacation_balance_days', $totalDeduction);

            $request->update([
                'status'         => VacationRequest::STATUS_APPROVED,
                'approved_by'    => $approver->id,
                'approver_notes' => $notes,
                'reviewed_at'    => now(),
            ]);

            $request->employee->user?->notify(new VacationRequestApprovedNotification($request));

            return $request->refresh();
        });
    }

    /**
     * Reject a pending vacation request.
     *
     * @throws ValidationException
     */
    public function reject(VacationRequest $request, User $approver, string $notes): VacationRequest
    {
        if (trim($notes) === '') {
            throw ValidationException::withMessages([
                'notes' => 'É obrigatório informar o motivo da rejeição.',
            ]);
        }

        $this->ensureCanBeReviewed($request);

        return DB::transaction(function () use ($request, $approver, $notes) {
            $request->update([
                'status'         => VacationRequest::STATUS_REJECTED,
                'approved_by'    => $approver->id,
                'approver_notes' => $notes,
                'reviewed_at'    => now(),
            ]);

            $request->employee->user?->notify(new VacationRequestRejectedNotification($request));

            return $request->refresh();
        });
    }

    /**
     * Cancel a request (can be done by the employee themselves or an admin).
     *
     * @throws ValidationException
     */
    public function cancel(VacationRequest $request, User $actor): VacationRequest
    {
        if ($request->status === VacationRequest::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => 'Esta solicitação já foi cancelada.',
            ]);
        }

        $wasApproved = $request->isApproved();

        return DB::transaction(function () use ($request, $wasApproved) {
            // Restore balance if cancelling an already approved request
            if ($wasApproved) {
                $totalDeduction = $request->days_requested + $request->sell_days;
                $request->employee->increment('vacation_balance_days', $totalDeduction);
            }

            $request->update([
                'status' => VacationRequest::STATUS_CANCELLED,
            ]);

            return $request->refresh();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate business rules before submission.
     *
     * @throws ValidationException
     */
    private function validateSubmission(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
        int $daysRequested,
        int $sellDays
    ): void {
        $errors = [];

        // 1. Minimum advance notice
        $advanceDays = (int) now()->diffInDays($startDate, false);
        if ($advanceDays < self::MIN_ADVANCE_DAYS) {
            $errors['start_date'] = "A solicitação deve ser feita com no mínimo " . self::MIN_ADVANCE_DAYS . " dias de antecedência. Faltam {$advanceDays} dias.";
        }

        // 2. Minimum vacation period (CLT)
        if ($daysRequested < self::MIN_DAYS_PER_PERIOD) {
            $errors['days_requested'] = "O período mínimo de férias é de " . self::MIN_DAYS_PER_PERIOD . " dias corridos.";
        }

        // 3. Sell days limit
        if ($sellDays > self::MAX_SELL_DAYS) {
            $errors['sell_days'] = "Máximo de " . self::MAX_SELL_DAYS . " dias para abono pecuniário.";
        }
        if ($sellDays > 0 && ($daysRequested + $sellDays) > $employee->vacation_balance_days) {
            $errors['sell_days'] = "Saldo insuficiente para incluir o abono. Saldo: {$employee->vacation_balance_days} dias.";
        }

        // 4. Balance check
        $totalNeeded = $daysRequested + $sellDays;
        if ($totalNeeded > $employee->vacation_balance_days) {
            $errors['balance'] = "Saldo insuficiente. Disponível: {$employee->vacation_balance_days} dias. Necessário: {$totalNeeded} dias.";
        }

        // 5. Date ordering
        if ($endDate->lt($startDate)) {
            $errors['end_date'] = 'A data de retorno não pode ser anterior à data de início.';
        }

        // 6. Overlap with an existing approved/pending request
        $overlap = VacationRequest::where('employee_id', $employee->id)
            ->whereIn('status', [VacationRequest::STATUS_PENDING, VacationRequest::STATUS_APPROVED])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->exists();

        if ($overlap) {
            $errors['start_date'] = 'Já existe uma solicitação de férias neste período.';
        }

        // 7. Concession period check
        if ($employee->vacation_concession_end) {
            $concessionEnd = Carbon::parse($employee->vacation_concession_end);
            if ($startDate->gt($concessionEnd)) {
                $errors['start_date'] = "As férias devem ser iniciadas até {$concessionEnd->format('d/m/Y')} (prazo de concessão).";
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Ensure request is in a reviewable state.
     *
     * @throws ValidationException
     */
    private function ensureCanBeReviewed(VacationRequest $request): void
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'status' => "Esta solicitação não pode mais ser alterada. Status atual: {$request->status_label}.",
            ]);
        }
    }

    /**
     * Count calendar days in a date range (inclusive).
     * For CLT, vacation is in calendar days, not working days.
     */
    private function countWorkingDays(Carbon $start, Carbon $end): int
    {
        return (int) $start->diffInDays($end) + 1;
    }
}
