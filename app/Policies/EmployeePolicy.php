<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

/**
 * EmployeePolicy
 *
 * Controls who can view or modify employee records.
 */
class EmployeePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->tenant_id !== $employee->tenant_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            $me = $user->employee;
            // Manager sees own record and subordinates
            return $me && (
                $me->id === $employee->id
                || $employee->manager_id === $me->id
            );
        }

        // Employee: own record only
        return $user->employee?->id === $employee->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Employee $employee): bool
    {
        if ($user->tenant_id !== $employee->tenant_id) {
            return false;
        }
        return $user->isAdmin();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->isAdmin() && $user->tenant_id === $employee->tenant_id;
    }
}
