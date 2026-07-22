<?php

namespace App\Models;

use App\Events\TenantCreated;
use App\Services\TenantProfileService;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

#[Fillable(['name', 'handle', 'user_id', 'theme_id', 'active', 'data', 'meta', 'config', 'phone', 'email', 'status', 'role', 'custom_domain', 'custom_domain_status'])]
#[Hidden(['deleted_at'])]
#[SoftDeletes]
class Tenant extends Model implements HasMedia
{
    use HasSubscriptions, HasUuid, InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'meta' => SchemalessAttributes::class,
            'config' => 'array',
            'active' => 'boolean',
        ];
    }

    protected $dispatchesEvents = [
        'created' => TenantCreated::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function themes(): MorphToMany
    {
        return $this->morphedByMany(Theme::class, 'tenantable')
            ->using(Tenantable::class)
            ->withPivot(['active', 'meta'])
            ->withTimestamps();
    }

    /** @var array<int, array<string, mixed>> */
    private array $themeSettingsCache = [];

    /**
     * @return array<string, mixed>
     */
    public function themeSettingsFor(int $themeId): array
    {
        if (array_key_exists($themeId, $this->themeSettingsCache)) {
            return $this->themeSettingsCache[$themeId];
        }

        $meta = $this->themes()->where('themes.id', $themeId)->first()?->pivot?->meta;

        return $this->themeSettingsCache[$themeId] = is_array($meta) ? $meta : [];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function saveThemeSettingsFor(int $themeId, array $options): void
    {
        $this->themes()->syncWithoutDetaching([
            $themeId => [
                'meta' => $options,
                'active' => true,
            ],
        ]);

        $this->themeSettingsCache[$themeId] = $options;
    }

    public function uploadThemeOptionMedia(int $themeId, string $optionKey, UploadedFile $file): string
    {
        $mediaDisk = config('media-library.disk_name', 'spaces');

        $previous = data_get($this->themeSettingsFor($themeId), $optionKey);

        if (is_string($previous) && filled($previous) && ! str_starts_with($previous, 'http')) {
            Storage::disk($mediaDisk)->delete($previous);
        }

        $this->getMedia('theme-options')
            ->filter(fn (Media $media): bool => (int) $media->getCustomProperty('theme_id') === $themeId
                && (string) $media->getCustomProperty('option_key') === $optionKey)
            ->each->delete();

        // Same path as logo / other dashboard uploads: storePublicly on spaces.
        return $file->storePublicly(
            'tenant-media/'.$this->uuid.'/theme-options',
            $mediaDisk,
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'));

        $this->addMediaCollection('theme-options')
            ->useDisk(config('media-library.disk_name'));
    }

    public function getUrlAttribute(): string
    {
        return route('tenant.home', $this->handle);
    }

    public function getLogoAttribute(): string
    {
        return app(TenantProfileService::class)->logo($this);
    }

    /**
     * Bio lives on tenants.meta — exposed so tenant('bio') and $tenant->bio work.
     */
    protected function bio(): Attribute
    {
        return Attribute::get(
            fn (): string => app(TenantProfileService::class)->bio($this)
        );
    }
}
