<?php

namespace App\Modules\Auditoria\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeModule($query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeEvent($query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    public function scopeSearch($query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term): void {
            $q->where('description', 'like', "%{$term}%")
                ->orWhere('module', 'like', "%{$term}%")
                ->orWhere('event', 'like', "%{$term}%");
        });
    }
}
