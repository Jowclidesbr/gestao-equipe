<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCandidate extends Model
{
    protected $fillable = [
        'job_opening_id', 'employee_id',
        'name', 'email', 'phone', 'linkedin_url',
        'resume_path', 'status', 'notes',
    ];

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'applied'    => 'Inscrito',
            'screening'  => 'Triagem',
            'interview'  => 'Entrevista',
            'technical'  => 'Teste Técnico',
            'offer'      => 'Proposta',
            'hired'      => 'Contratado',
            'rejected'   => 'Rejeitado',
            default      => '—',
        };
    }
}
