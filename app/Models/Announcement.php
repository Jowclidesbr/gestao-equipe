<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'author_id', 'department_id',
        'title', 'body', 'priority', 'audience', 'team_filter',
        'is_pinned', 'published_at', 'expires_at',
    ];

    protected $casts = [
        'is_pinned'    => 'boolean',
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }

    public function reads(): HasMany { return $this->hasMany(AnnouncementRead::class); }

    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }
}
