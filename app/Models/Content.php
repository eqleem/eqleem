<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id',
    'block_id',
    'parent_id',
    'type',
    'title',
    'slug',
    'data',
    'meta',
    'active',
    'status',
    'sort_order',
    'published_at',
])]
class Content extends Model
{
    use BelongsToTenant, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'meta' => 'array',
            'active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if ($tenantId = currentTenantId()) {
                $builder->where('tenant_id', $tenantId);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getAvatarAttribute(): string
    {
        return 'https://api.dicebear.com/9.x/shapes/svg?seed='.$this->id;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'منشور',
            default => 'مسودة',
        };
    }

    // public function block(): BelongsTo
    // {
    //     return $this->belongsTo(Block::class);
    // }

}
