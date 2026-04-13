<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Models\JobOpening;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query   = '';
    public bool   $isOpen  = false;

    public function updatedQuery(): void
    {
        $this->isOpen = strlen($this->query) >= 2;
    }

    public function close(): void
    {
        $this->query  = '';
        $this->isOpen = false;
    }

    public function render()
    {
        $employees   = collect();
        $jobOpenings = collect();

        if ($this->isOpen) {
            $tenantId = auth()->user()->tenant_id;
            $term     = $this->query;

            $employees = Employee::with('user', 'department', 'jobPosition')
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->whereHas('user', fn($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"))
                ->orWhere(fn($q) => $q->where('cpf', 'like', "%{$term}%")
                    ->when($tenantId, fn($q2) => $q2->where('tenant_id', $tenantId)))
                ->limit(5)
                ->get();

            $jobOpenings = JobOpening::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->where('title', 'like', "%{$term}%")
                ->limit(3)
                ->get();
        }

        return view('livewire.global-search', compact('employees', 'jobOpenings'));
    }
}
