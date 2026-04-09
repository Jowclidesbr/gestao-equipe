<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VacationRequest;

/**
 * VacationRequestPolicy
 *
 * Ensures strict tenant & ownership isolation.
 * An employee can NEVER see or act on another employee's vacation request.
 */
class VacationRequestPolicy
{
    /**
     * SuperAdmin can do anything.
     * Used as the "before" hook – if returns non-null, no further checks run.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * List vacation requests.
     * Admins/Managers see all team requests; Employees only see their own.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * View a single request.
     */
    public function view(User $user, VacationRequest $vacationRequest): bool
    {
        // Must be same tenant
        if ($user->tenant_id !== $vacationRequest->tenant_id) {
            return false;
        }

        // Admins can see everything within the tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Managers can see requests from their subordinates or themselves
        if ($user->isManager()) {
            $managerEmployee  = $user->employee;
            $requestEmployee  = $vacationRequest->employee;

            if (! $managerEmployee || ! $requestEmployee) {
                return false;
            }

            return $requestEmployee->manager_id === $managerEmployee->id
                || $requestEmployee->id === $managerEmployee->id;
        }

        // Employee can only see their own
        return $user->employee?->id === $vacationRequest->employee_id;
    }

    /**
     * Create a new request (employees submit for themselves only).
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->employee !== null;
    }

    /**
     * Approve a vacation request.
     * Only Managers (for their team) and Admins.
     */
    public function approve(User $user, VacationRequest $vacationRequest): bool
    {
        if ($user->tenant_id !== $vacationRequest->tenant_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            $managerEmployee = $user->employee;
            return $managerEmployee
                && $vacationRequest->employee->manager_id === $managerEmployee->id;
        }

        return false;
    }

    /**
     * Reject a vacation request (same rules as approve).
     */
    public function reject(User $user, VacationRequest $vacationRequest): bool
    {
        return $this->approve($user, $vacationRequest);
    }

    /**
     * Cancel a request.
     * Employee can cancel their own (if still pending).
     * Admins/Managers can cancel any request.
     */
    public function cancel(User $user, VacationRequest $vacationRequest): bool
    {
        if ($user->tenant_id !== $vacationRequest->tenant_id) {
            return false;
        }

        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        // Employee can only cancel their own PENDING request
        return $user->employee?->id === $vacationRequest->employee_id
            && $vacationRequest->isPending();
    }
}
