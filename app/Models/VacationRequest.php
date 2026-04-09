<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'employee_id', 'approved_by',
        'start_date', 'end_date', 'days_requested', 'sell_days',
        'status', 'employee_notes', 'approver_notes',
        'submitted_at', 'reviewed_at', 'balance_snapshot',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'submitted_at'     => 'datetime',
        'reviewed_at'      => 'datetime',
        'days_requested'   => 'integer',
        'sell_days'        => 'integer',
        'balance_snapshot' => 'integer',
    ];

    // ─── Status constants ─────────────────────────────────────────────────────

    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // ─── Relationships ────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'Aguardando',
            'approved'  => 'Aprovado',
            'rejected'  => 'Rejeitado',
            'cancelled' => 'Cancelado',
            default     => '—',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'badge-pending',
            'approved'  => 'badge-approved',
            'rejected'  => 'badge-rejected',
            'cancelled' => 'badge-inactive',
            default     => 'badge',
        };
    }
}
