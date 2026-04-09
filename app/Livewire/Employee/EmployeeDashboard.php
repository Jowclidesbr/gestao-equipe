<?php

namespace App\Livewire\Employee;

use App\Services\DashboardMetricsService;
use Livewire\Component;

class EmployeeDashboard extends Component
{
    public array $metrics = [];

    public function mount(DashboardMetricsService $service): void
    {
        $employee = auth()->user()->employee;
        if ($employee) {
            $this->metrics = $service->getEmployeeMetrics($employee->id);
        }
    }

    public function render()
    {
        return view('livewire.employee.employee-dashboard')
            ->layout('layouts.app', ['title' => 'Meu Painel']);
    }
}
