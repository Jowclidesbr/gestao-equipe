<?php

namespace App\Http\Controllers;

use App\Exports\AbsencesExport;
use App\Exports\EmployeesExport;
use App\Exports\VacationsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Absence;
use App\Models\Employee;
use App\Models\VacationRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // ── Employees ────────────────────────────────────────────────────────

    public function employeesXlsx(Request $request)
    {
        $user = auth()->user();
        return Excel::download(
            new EmployeesExport($user->tenant_id, $request->status ?? '', $request->dept ?? ''),
            'colaboradores_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function employeesPdf(Request $request)
    {
        $user = auth()->user();
        $employees = Employee::with('user', 'department', 'jobPosition')
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->dept, fn($q) => $q->where('department_id', $request->dept))
            ->orderBy('id')->get();

        $pdf = Pdf::loadView('exports.employees-pdf', compact('employees'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('colaboradores_' . now()->format('Ymd_His') . '.pdf');
    }

    // ── Vacations ────────────────────────────────────────────────────────

    public function vacationsXlsx(Request $request)
    {
        $user = auth()->user();
        return Excel::download(
            new VacationsExport($user->tenant_id, $request->status ?? ''),
            'ferias_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function vacationsPdf(Request $request)
    {
        $user = auth()->user();
        $vacations = VacationRequest::with('employee.user', 'employee.department')
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('submitted_at')->get();

        $pdf = Pdf::loadView('exports.vacations-pdf', compact('vacations'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('ferias_' . now()->format('Ymd_His') . '.pdf');
    }

    // ── Absences ─────────────────────────────────────────────────────────

    public function absencesXlsx(Request $request)
    {
        $user = auth()->user();
        return Excel::download(
            new AbsencesExport($user->tenant_id, $request->type ?? ''),
            'afastamentos_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function absencesPdf(Request $request)
    {
        $user = auth()->user();
        $absences = Absence::with('employee.user', 'employee.department')
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->orderByDesc('start_date')->get();

        $pdf = Pdf::loadView('exports.absences-pdf', compact('absences'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('afastamentos_' . now()->format('Ymd_His') . '.pdf');
    }
}
