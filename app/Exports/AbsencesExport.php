<?php

namespace App\Exports;

use App\Models\Absence;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AbsencesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?int $tenantId = null,
        protected string $type = '',
    ) {}

    public function query()
    {
        return Absence::with('employee.user', 'employee.department')
            ->when($this->tenantId, fn($q) => $q->where('tenant_id', $this->tenantId))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->orderByDesc('start_date');
    }

    public function headings(): array
    {
        return ['ID', 'Colaborador', 'Departamento', 'Tipo', 'Início', 'Fim', 'Dias', 'CID', 'Observações'];
    }

    public function map($ab): array
    {
        return [
            $ab->id,
            $ab->employee->user->name ?? '',
            $ab->employee->department->name ?? '',
            $ab->type_label,
            $ab->start_date->format('d/m/Y'),
            $ab->end_date->format('d/m/Y'),
            $ab->start_date->diffInDays($ab->end_date) + 1,
            $ab->cid_code ?? '',
            $ab->notes ?? '',
        ];
    }
}
