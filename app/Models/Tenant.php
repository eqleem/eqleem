<?php

namespace App\Models;

use App\Events\TenantCreated;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

#[Fillable(['name', 'handle', 'user_id', 'theme_id', 'active', 'data', 'meta', 'config', 'phone', 'email', 'status', 'role'])]
#[Hidden(['deleted_at'])]
#[SoftDeletes]
class Tenant extends Model implements HasMedia
{
    use HasUuid, InteractsWithMedia;

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

    public function themes(): MorphToMany
    {
        return $this->morphedByMany(Theme::class, 'tenantable')
            ->using(Tenantable::class)
            ->withPivot(['active', 'meta'])
            ->withTimestamps();
    }

    /**
     * @return array<string, mixed>
     */
    public function themeSettingsFor(int $themeId): array
    {
        $meta = $this->themes()->where('themes.id', $themeId)->first()?->pivot?->meta;

        return is_array($meta) ? $meta : [];
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
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'));
    }

    public function getUrlAttribute(): string
    {
        return route('tenant.home', $this->handle);
    }

    public function getLogoAttribute(): string
    {
        $stored = data_get($this->meta, 'logo') ?? data_get($this->data, 'logo');

        if (Str::startsWith((string) $stored, 'http')) {
            return (string) $stored;
        }

        $path = is_array($stored) ? ($stored['path'] ?? null) : $stored;

        if (filled($path)) {
            return Storage::url((string) $path);
        }

        return 'https://api.dicebear.com/9.x/shapes/svg?seed='.$this->uuid;
    }
}
