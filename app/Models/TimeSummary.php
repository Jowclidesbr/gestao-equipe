<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeSummary extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'date',
        'worked_minutes', 'expected_minutes', 'overtime_minutes',
        'deficit_minutes', 'break_minutes', 'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }

    public function getWorkedFormatAttribute(): string
    {
        return intdiv($this->worked_minutes, 60) . 'h' . str_pad($this->worked_minutes % 60, 2, '0', STR_PAD_LEFT);
    }

    public function getOvertimeFormatAttribute(): string
    {
        return intdiv($this->overtime_minutes, 60) . 'h' . str_pad($this->overtime_minutes % 60, 2, '0', STR_PAD_LEFT);
    }

    public function getDeficitFormatAttribute(): string
    {
        return intdiv($this->deficit_minutes, 60) . 'h' . str_pad($this->deficit_minutes % 60, 2, '0', STR_PAD_LEFT);
    }
}
