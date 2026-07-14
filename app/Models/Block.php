<?php

namespace App\Models;

// use App\PageBuilder\BlockRegistry;
use App\Support\TenantPageBlocks;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $component
 * @property string $type
 * @property int|null $parent_id
 * @property int|null $content_id
 * @property string|null $title
 * @property string|null $slug
 * @property int $sort_order
 * @property string $position
 * @property string $status
 * @property array<string, mixed>|null $data
 * @property string|null $variant
 * @property Carbon|null $published_at
 * @property bool $active
 * @property bool $is_default
 */
#[Fillable([
    'tenant_id',
    'component',
    'type',
    'parent_id',
    'content_id',
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
class Block extends Model implements HasMedia
{
    use BelongsToTenant, HasUuid, InteractsWithMedia;

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('editor-images')
            ->useDisk(config('media-library.disk_name'));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
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

    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $tenantId = currentTenantId();

        return $query->when($tenantId, fn (Builder $query): Builder => $query->where('tenant_id', $tenantId));
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeUserBlocks(Builder $query): Builder
    {
        return $query->where('is_default', false);
    }

    public function scopeActiveOnHome(Builder $query): Builder
    {
        return $query->where('active', true)->where('position', 'home');
    }

    public static function queryForTenantRoots(): Builder
    {
        // Tenant scoping comes from BelongsToTenant global scope.
        return static::query()->roots()->whereNull('content_id');
    }

    public static function queryForContent(int $contentId): Builder
    {
        return static::query()
            ->roots()
            ->where('content_id', $contentId);
    }

    public static function findSingleton(string $type): ?self
    {
        return static::queryForTenantRoots()->type($type)->first();
    }

    /**
     * @param  list<string>  $types
     */
    public static function findPageBlock(int $id, array $types): ?self
    {
        $cached = app(TenantPageBlocks::class)->pageBlock($id, $types);

        if ($cached) {
            return $cached;
        }

        return static::queryForTenantRoots()
            ->whereKey($id)
            ->whereIn('type', $types)
            ->first();
    }

    /**
     * Active home-page blocks for the current tenant (from the request cache).
     *
     * @return Collection<int, Block>
     */
    public static function homePageBlocks(): Collection
    {
        return app(TenantPageBlocks::class)->homeBlocks();
    }

    /**
     * @return Collection<int, Content>
     */
    public function activeContents(string $type): Collection
    {
        if ($this->relationLoaded('contents')) {
            return $this->contents
                ->where('type', $type)
                ->where('active', true)
                ->sortBy('sort_order')
                ->values();
        }

        return $this->contents()
            ->type($type)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();
    }

    // public function formSubmissions(): HasMany
    // {
    //     return $this->hasMany(FormSubmission::class);
    // }
}
