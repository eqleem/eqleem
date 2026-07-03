<?php

namespace App\Models;

use Aliziodev\LaravelTaxonomy\Traits\HasTaxonomy;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
class Content extends Model implements HasMedia
{
    use BelongsToTenant, HasTaxonomy, HasUuid, InteractsWithMedia, SoftDeletes;

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

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'content_id');
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('editor-images')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('portfolio-media')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('store-media')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('service-media')
            ->useDisk(config('media-library.disk_name'));
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function portfolioImages(): array
    {
        return $this->getMedia('portfolio-media')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'url' => $media->getUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function portfolioImageUrls(): array
    {
        return collect($this->portfolioImages())
            ->pluck('url')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function storeImages(): array
    {
        return $this->getMedia('store-media')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'url' => $media->getUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function storeImageUrls(): array
    {
        return collect($this->storeImages())
            ->pluck('url')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function serviceImages(): array
    {
        return $this->getMedia('service-media')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'url' => $media->getUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function serviceImageUrls(): array
    {
        return collect($this->serviceImages())
            ->pluck('url')
            ->values()
            ->all();
    }

    public function calendars(): MorphToMany
    {
        return $this->morphToMany(Calendar::class, 'bookable', 'bookables')
            ->withPivot(['type', 'active'])
            ->withTimestamps();
    }

    public function migrateLegacyPortfolioImagesIfNeeded(): void
    {
        if ($this->type !== 'portfolio') {
            return;
        }

        $legacyImages = data_get($this->data, 'images', []);

        if (! is_array($legacyImages) || $legacyImages === []) {
            return;
        }

        if ($this->getMedia('portfolio-media')->isEmpty()) {
            foreach ($legacyImages as $path) {
                if (! filled($path) || ! is_string($path)) {
                    continue;
                }

                $disk = config('media-library.disk_name');

                if (! Storage::disk($disk)->exists($path)) {
                    continue;
                }

                $this->addMediaFromDisk($path, $disk)
                    ->preservingOriginal()
                    ->toMediaCollection('portfolio-media');
            }
        }

        $data = $this->data ?? [];
        unset($data['images']);
        $this->forceFill(['data' => $data])->save();
    }

    public function migrateLegacyBlogCategoriesIfNeeded(): void
    {
        if ($this->type !== 'blog') {
            return;
        }

        if ($this->taxonomies()->where('type', 'blog_category')->exists()) {
            return;
        }

        $legacyIds = legacyBlogCategoryIdsFromData($this->data);

        if ($legacyIds === []) {
            return;
        }

        $this->syncTaxonomiesOfType('blog_category', $legacyIds);

        $data = $this->data ?? [];
        unset($data['category_ids'], $data['category_id']);
        $this->forceFill(['data' => $data])->save();
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('sort_order');
    }
}
