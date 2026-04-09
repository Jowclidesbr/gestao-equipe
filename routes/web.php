<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Department\DepartmentList;
use App\Livewire\Employee\EmployeeDashboard;
use App\Livewire\Employee\EmployeeList;
use App\Livewire\Vacation\VacationRequestForm;
use App\Livewire\Vacation\VacationRequestList;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
//  AUTH ROUTES
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
         ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
     ->name('logout')
     ->middleware('auth');

// ─────────────────────────────────────────────────────────────────────────────
//  AUTHENTICATED + TENANT-SCOPED ROUTES
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant', 'tenant.scope'])->group(function () {

    // ── Root redirect based on role ──────────────────────────────────────────
    Route::get('/', function () {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isManager()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('employee.dashboard');
    })->name('home')->withoutMiddleware(['tenant', 'tenant.scope']);

    // ────────────────────────────────────────────────────────────────────────
    //  ADMIN / MANAGER AREA
    // ────────────────────────────────────────────────────────────────────────
    Route::middleware('role:super_admin|admin|manager')
         ->prefix('admin')
         ->name('admin.')
         ->group(function () {

        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');

        // Employees
        Route::get('/employees', EmployeeList::class)->name('employees.index');
        Route::get('/employees/create', EmployeeList::class)->name('employees.create')
             ->middleware('role:super_admin|admin');

        // Vacations (approval queue)
        Route::get('/vacations', VacationRequestList::class)->name('vacations.index');

        // Departments
        Route::get('/departments', DepartmentList::class)->name('departments.index');

        // ATS – Job Openings
        Route::get('/job-openings', \App\Livewire\Ats\JobOpeningList::class)->name('job-openings.index');
    });

    // ────────────────────────────────────────────────────────────────────────
    //  EMPLOYEE PORTAL (all authenticated users)
    // ────────────────────────────────────────────────────────────────────────
    Route::prefix('portal')
         ->name('employee.')
         ->group(function () {

        Route::get('/dashboard', EmployeeDashboard::class)->name('dashboard');

        // My vacations
        Route::get('/ferias', VacationRequestList::class)->name('vacation.index');
        Route::get('/ferias/solicitar', VacationRequestForm::class)->name('vacation.create');
    });
});
