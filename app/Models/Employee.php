<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'user_id', 'department_id', 'job_position_id', 'manager_id',
        'employee_code', 'cpf', 'rg', 'phone', 'mobile', 'birth_date', 'gender',
        'address', 'city', 'state', 'cep',
        'admission_date', 'dismissal_date', 'contract_type', 'work_mode', 'presence_days',
        'salary', 'status',
        'vacation_balance_days', 'vacation_acquisition_start',
        'vacation_acquisition_end', 'vacation_concession_end',
    ];

    protected $casts = [
        'birth_date'                 => 'date',
        'admission_date'             => 'date',
        'dismissal_date'             => 'date',
        'vacation_acquisition_start' => 'date',
        'vacation_acquisition_end'   => 'date',
        'vacation_concession_end'    => 'date',
        'salary'                     => 'decimal:2',
        'vacation_balance_days'      => 'integer',
        'presence_days'              => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function vacationRequests(): HasMany
    {
        return $this->hasMany(VacationRequest::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function positionHistory(): HasMany
    {
        return $this->hasMany(EmployeePositionHistory::class)->orderByDesc('effective_date');
    }

    // ─── Computed / Helpers ───────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Days worked since admission (used for turnover & metrics).
     */
    public function getTenureInDaysAttribute(): int
    {
        $end = $this->dismissal_date ?? Carbon::today();
        return (int) $this->admission_date->diffInDays($end);
    }

    public function isBirthdayThisMonth(): bool
    {
        return $this->birth_date?->month === Carbon::now()->month;
    }
}
