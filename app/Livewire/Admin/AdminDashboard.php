<?php

namespace App\Livewire\Admin;

use App\Services\DashboardMetricsService;
use Livewire\Component;

class AdminDashboard extends Component
{
    public array $metrics = [];
    public bool $loading  = true;

    public function mount(DashboardMetricsService $metricsService): void
    {
        $user          = auth()->user();
        $tenantId      = $user->tenant_id ?? null; // super_admin has no tenant
        $this->metrics = $metricsService->getAdminMetrics($tenantId);
        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.admin.admin-dashboard')
            ->layout('layouts.app', ['title' => 'Dashboard Administrativo']);
    }
}
