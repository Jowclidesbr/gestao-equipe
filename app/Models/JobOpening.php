<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOpening extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'department_id', 'job_position_id', 'created_by',
        'title', 'description', 'requirements',
        'type', 'mode', 'status', 'vacancies',
        'deadline', 'salary_offered',
    ];

    protected $casts = [
        'deadline'       => 'date',
        'salary_offered' => 'decimal:2',
        'vacancies'      => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(JobCandidate::class);
    }
}
