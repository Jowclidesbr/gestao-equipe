<?php

namespace App\Exports;

use App\Models\VacationRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VacationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?int $tenantId = null,
        protected string $status = '',
    ) {}

    public function query()
    {
        return VacationRequest::with('employee.user', 'employee.department')
            ->when($this->tenantId, fn($q) => $q->where('tenant_id', $this->tenantId))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderByDesc('submitted_at');
    }

    public function headings(): array
    {
        return ['ID', 'Colaborador', 'Departamento', 'Início', 'Fim', 'Dias', 'Abono', 'Status', 'Submetido em'];
    }

    public function map($vr): array
    {
        return [
            $vr->id,
            $vr->employee->user->name ?? '',
            $vr->employee->department->name ?? '',
            $vr->start_date->format('d/m/Y'),
            $vr->end_date->format('d/m/Y'),
            $vr->days_requested,
            $vr->sell_days,
            $vr->status_label,
            $vr->submitted_at?->format('d/m/Y H:i') ?? '',
        ];
    }
}
