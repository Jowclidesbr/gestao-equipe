<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?int $tenantId = null,
        protected string $status = '',
        protected string $dept = '',
    ) {}

    public function query()
    {
        return Employee::with('user', 'department', 'jobPosition')
            ->when($this->tenantId, fn($q) => $q->where('tenant_id', $this->tenantId))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dept, fn($q) => $q->where('department_id', $this->dept))
            ->orderBy('id');
    }

    public function headings(): array
    {
        return ['ID', 'Nome', 'E-mail', 'CPF', 'Departamento', 'Cargo', 'Contrato', 'Turno', 'Equipe', 'Admissão', 'Status'];
    }

    public function map($emp): array
    {
        $shiftLabels = ['I' => 'I — 08h–17h', 'II' => 'II — 15h–00h', 'III' => 'III — 00h–08h'];
        $teamLabels  = ['run_the_bank' => 'Run The Bank', 'change_the_bank' => 'Change The Bank'];
        $statusLabels = ['active' => 'Ativo', 'inactive' => 'Inativo', 'on_leave' => 'Licença', 'terminated' => 'Desligado'];

        return [
            $emp->id,
            $emp->user->name ?? '',
            $emp->user->email ?? '',
            $emp->cpf ?? '',
            $emp->department->name ?? '',
            $emp->jobPosition->title ?? '',
            strtoupper($emp->contract_type),
            $shiftLabels[$emp->shift] ?? '',
            $teamLabels[$emp->team] ?? '',
            $emp->admission_date?->format('d/m/Y') ?? '',
            $statusLabels[$emp->status] ?? $emp->status,
        ];
    }
}
