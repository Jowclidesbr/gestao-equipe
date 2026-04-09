<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'employee_id', 'registered_by',
        'type', 'start_date', 'end_date',
        'notes', 'cid_code', 'document_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'sick_leave'  => 'Licença Médica',
            'accident'    => 'Acidente de Trabalho',
            'maternity'   => 'Licença Maternidade',
            'paternity'   => 'Licença Paternidade',
            'bereavement' => 'Luto',
            'jury_duty'   => 'Serviço Jurídico',
            'unpaid'      => 'Sem Remuneração',
            'other'       => 'Outro',
            default       => '—',
        };
    }
}
