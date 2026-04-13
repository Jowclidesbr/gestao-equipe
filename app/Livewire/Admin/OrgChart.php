<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Employee;
use Livewire\Component;

class OrgChart extends Component
{
    public string $viewMode = 'department'; // department | manager

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        if ($this->viewMode === 'department') {
            $tree = $this->buildDepartmentTree($tenantId);
        } else {
            $tree = $this->buildManagerTree($tenantId);
        }

        return view('livewire.admin.org-chart', compact('tree'))
            ->layout('layouts.app', ['title' => 'Organograma']);
    }

    private function buildDepartmentTree(?int $tenantId): array
    {
        $departments = Department::with(['employees.user', 'employees.jobPosition'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $byParent = $departments->groupBy(fn($d) => $d->parent_id ?? 0);

        return $this->nestDepartments($byParent, 0);
    }

    private function nestDepartments($byParent, $parentId): array
    {
        $nodes = [];
        foreach ($byParent->get($parentId, []) as $dept) {
            $activeEmployees = $dept->employees->where('status', 'active');
            $nodes[] = [
                'id'        => $dept->id,
                'name'      => $dept->name,
                'code'      => $dept->code,
                'type'      => 'department',
                'count'     => $activeEmployees->count(),
                'employees' => $activeEmployees->map(fn($e) => [
                    'name'     => $e->user->name ?? '—',
                    'position' => $e->jobPosition->title ?? '—',
                    'avatar'   => $e->user->avatar_url ?? '',
                    'initials' => collect(explode(' ', $e->user->name ?? ''))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join(''),
                ])->values()->toArray(),
                'children'  => $this->nestDepartments($byParent, $dept->id),
            ];
        }
        return $nodes;
    }

    private function buildManagerTree(?int $tenantId): array
    {
        $employees = Employee::with(['user', 'jobPosition', 'department'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->get();

        $byManager = $employees->groupBy(fn($e) => $e->manager_id ?? 0);

        // Root nodes = employees with no manager
        return $this->nestEmployees($byManager, 0);
    }

    private function nestEmployees($byManager, $managerId): array
    {
        $nodes = [];
        foreach ($byManager->get($managerId, []) as $emp) {
            $nodes[] = [
                'id'         => $emp->id,
                'name'       => $emp->user->name ?? '—',
                'position'   => $emp->jobPosition->title ?? '—',
                'department' => $emp->department->name ?? '—',
                'avatar'     => $emp->user->avatar_url ?? '',
                'initials'   => collect(explode(' ', $emp->user->name ?? ''))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join(''),
                'type'       => 'employee',
                'children'   => $this->nestEmployees($byManager, $emp->id),
            ];
        }
        return $nodes;
    }
}
