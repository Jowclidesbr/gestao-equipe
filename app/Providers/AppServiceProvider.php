<?php

namespace App\Providers;

use App\Http\Middleware\EnsureTenantScope;
use App\Http\Middleware\TenantMiddleware;
use App\Models\Employee;
use App\Models\VacationRequest;
use App\Policies\EmployeePolicy;
use App\Policies\VacationRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Policy mappings.
     */
    protected $policies = [
        VacationRequest::class => VacationRequestPolicy::class,
        Employee::class        => EmployeePolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();

        // Fix Livewire script + update URLs for XAMPP subdirectory install.
        // Without this, Livewire generates root-relative URLs (/livewire/...)
        // which resolve to http://localhost/livewire/... (404) instead of
        // http://localhost/gestao-equipe/public/livewire/...
        Livewire::setScriptRoute(function ($handle) {
            return Route::get('livewire/livewire.js', $handle);
        });

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('livewire/update', $handle)->middleware('web');
        });

        // Gate for SuperAdmin bypass (already handled in policies via before())
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });
    }
}
