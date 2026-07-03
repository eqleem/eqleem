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
use Illuminate\Support\Carbon;
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

        $this->addMediaCollection('digital-product-media')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('digital-product-downloads')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('digital-service-media')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('menu-media')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('course-media')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('course-lesson-files')
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('unit-media')
            ->useDisk(config('media-library.disk_name'));
    }

    public function hasMediaAtPath(string $collection, string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        return $this->getMedia($collection)
            ->contains(fn (Media $media): bool => $media->getPathRelativeToRoot() === $path
                || $media->getUrl() === $path);
    }

    public function attachMediaFromDiskIfNeeded(string $collection, string $path): void
    {
        if (! filled($path) || $this->hasMediaAtPath($collection, $path)) {
            return;
        }

        $disk = config('media-library.disk_name');

        if (! Storage::disk($disk)->exists($path)) {
            return;
        }

        $this->addMediaFromDisk($path, $disk)
            ->preservingOriginal()
            ->toMediaCollection($collection);
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

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function unitImages(): array
    {
        return $this->getMedia('unit-media')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'url' => $media->getUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function digitalProductImages(): array
    {
        return $this->getMedia('digital-product-media')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'url' => $media->getUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, size: int}>
     */
    public function digitalProductDownloadFiles(): array
    {
        return $this->getMedia('digital-product-downloads')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'size' => (int) $media->size,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function menuImages(): array
    {
        return $this->getMedia('menu-media')
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
    public function menuImageUrls(): array
    {
        return collect($this->menuImages())
            ->pluck('url')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function digitalServiceImages(): array
    {
        return $this->getMedia('digital-service-media')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'url' => $media->getUrl(),
            ])
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

    /**
     * @return array<string, string>
     */
    public static function newsletterMailStatusOptions(): array
    {
        return [
            'draft' => 'مسودة',
            'scheduled' => 'مجدولة',
            'sent' => 'تم الإرسال',
        ];
    }

    public function newsletterMailStatus(): string
    {
        $status = (string) data_get($this->data, 'mail_status', 'draft');

        return array_key_exists($status, self::newsletterMailStatusOptions())
            ? $status
            : 'draft';
    }

    public function getNewsletterMailStatusLabelAttribute(): string
    {
        return self::newsletterMailStatusOptions()[$this->newsletterMailStatus()] ?? 'مسودة';
    }

    public function newsletterSentAt(): ?Carbon
    {
        $value = data_get($this->data, 'sent_at');

        return filled($value) ? Carbon::parse($value) : null;
    }

    public function newsletterScheduledAt(): ?Carbon
    {
        $value = data_get($this->data, 'scheduled_at');

        return filled($value) ? Carbon::parse($value) : null;
    }

    public function newsletterRecipientsCount(): int
    {
        return (int) data_get($this->data, 'recipients_count', 0);
    }

    /**
     * @return array<int, array{id: int, url: string}>
     */
    public function courseImages(): array
    {
        return $this->getMedia('course-media')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'url' => $media->getUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, size: int, chapter_id: ?string, lesson_id: ?string}>
     */
    public function courseLessonFiles(): array
    {
        return $this->getMedia('course-lesson-files')
            ->map(fn (Media $media): array => [
                'id' => (int) $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'size' => (int) $media->size,
                'chapter_id' => $media->getCustomProperty('chapter_id'),
                'lesson_id' => $media->getCustomProperty('lesson_id'),
            ])
            ->values()
            ->all();
    }

    public function courseLessonCount(): int
    {
        return collect(data_get($this->data, 'chapters', []))
            ->flatMap(fn (mixed $chapter): array => is_array($chapter) ? ($chapter['lessons'] ?? []) : [])
            ->count();
    }

    /**
     * @return array<string, string>
     */
    public static function courseLevelOptions(): array
    {
        return [
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
            'none' => 'بدون تصنيف',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function courseTypeOptions(): array
    {
        return [
            'recorded' => 'مسجلة',
            'live' => 'مباشرة',
            'hybrid' => 'مختلطة',
        ];
    }

    public function courseLevelLabel(): string
    {
        $level = (string) data_get($this->data, 'level', 'none');

        return self::courseLevelOptions()[$level] ?? self::courseLevelOptions()['none'];
    }
}
