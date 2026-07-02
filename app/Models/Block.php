<?php

namespace App\Models;

// use App\PageBuilder\BlockRegistry;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $component
 * @property string $type
 * @property int|null $parent_id
 * @property string|null $title
 * @property string|null $slug
 * @property int $sort_order
 * @property string $position
 * @property string $status
 * @property array<string, mixed>|null $data
 * @property Carbon|null $published_at
 * @property bool $active
 * @property bool $is_default
 */
#[Fillable([
    'tenant_id',
    'component',
    'type',
    'parent_id',
    'title',
    'slug',
    'variant',
    'sort_order',
    'position',
    'status',
    'active',
    'is_default',
    'data',
    'published_at',
])]
class Block extends Model
{
    use BelongsToTenant, HasUuid;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'published_at' => 'datetime',
            'active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BlockTranslation::class);
    }

    public function translation(?string $locale = null): HasOne
    {
        return $this->hasOne(BlockTranslation::class)
            ->where('locale', $locale ?? app()->getLocale());
    }

    public function isPublished(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        return is_null($this->published_at) || $this->published_at->isPast();
    }

    // public function isSellable(): bool
    // {
    //     return BlockRegistry::find($this->component)?->isSellable() ?? false;
    // }

    /**
     * @param  Builder<Block>  $query
     * @return Builder<Block>
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // public function formSubmissions(): HasMany
    // {
    //     return $this->hasMany(FormSubmission::class);
    // }
}
