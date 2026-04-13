<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
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
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
         ->middleware('throttle:5,1');

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])
         ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
         ->name('password.email')
         ->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])
         ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
         ->name('password.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
     ->name('logout')
     ->middleware('auth');

// ─────────────────────────────────────────────────────────────────────────────
//  AUTHENTICATED + TENANT-SCOPED ROUTES
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant', 'tenant.scope'])->group(function () {

    // ── Profile & Notifications ──────────────────────────────────────────────
    Route::get('/profile', \App\Livewire\User\ProfileEdit::class)
         ->name('profile.edit')
         ->withoutMiddleware(['tenant', 'tenant.scope']);

    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read-all')->withoutMiddleware(['tenant', 'tenant.scope']);

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
             ->middleware('role:super_admin|admin|manager');

        // Vacations (approval queue)
        Route::get('/vacations', VacationRequestList::class)->name('vacations.index');

        // Vacation Calendar
        Route::get('/vacation-calendar', \App\Livewire\Vacation\VacationCalendar::class)->name('vacation-calendar.index');

        // Absences
        Route::get('/absences', \App\Livewire\Absence\AbsenceManager::class)->name('absences.index');

        // Departments
        Route::get('/departments', DepartmentList::class)->name('departments.index');

        // ATS – Job Openings
        Route::get('/job-openings', \App\Livewire\Ats\JobOpeningList::class)->name('job-openings.index');
        Route::get('/job-openings/{opening}/pipeline', \App\Livewire\Ats\KanbanPipeline::class)->name('job-openings.pipeline');

        // Org Chart
        Route::get('/orgchart', \App\Livewire\Admin\OrgChart::class)->name('orgchart.index');

        // Time Clock
        Route::get('/time-clock', \App\Livewire\TimeClock\TimeClockManager::class)->name('time-clock.index');

        // Documents
        Route::get('/documents', \App\Livewire\Employee\DocumentManager::class)->name('documents.index');

        // Communication
        Route::get('/announcements', \App\Livewire\Communication\AnnouncementBoard::class)->name('announcements.index');

        // Reports
        Route::get('/reports', \App\Livewire\Admin\Reports::class)->name('reports.index');

        // Exports
        Route::prefix('export')->name('export.')->controller(\App\Http\Controllers\ExportController::class)->group(function () {
            Route::get('/employees/xlsx', 'employeesXlsx')->name('employees.xlsx');
            Route::get('/employees/pdf',  'employeesPdf')->name('employees.pdf');
            Route::get('/vacations/xlsx', 'vacationsXlsx')->name('vacations.xlsx');
            Route::get('/vacations/pdf',  'vacationsPdf')->name('vacations.pdf');
            Route::get('/absences/xlsx',  'absencesXlsx')->name('absences.xlsx');
            Route::get('/absences/pdf',   'absencesPdf')->name('absences.pdf');
        });
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

        // My Time Clock
        Route::get('/ponto', \App\Livewire\TimeClock\TimeClockManager::class)->name('time-clock.index');

        // My Documents
        Route::get('/documentos', \App\Livewire\Employee\DocumentManager::class)->name('documents.index');

        // Announcements
        Route::get('/comunicados', \App\Livewire\Communication\AnnouncementBoard::class)->name('announcements.index');
    });
});
