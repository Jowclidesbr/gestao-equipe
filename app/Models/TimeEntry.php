<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'date', 'type', 'time', 'note', 'ip_address',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'clock_in'  => 'Entrada',
            'break_out' => 'Saída Intervalo',
            'break_in'  => 'Retorno Intervalo',
            'clock_out' => 'Saída',
            default     => $this->type,
        };
    }
}
