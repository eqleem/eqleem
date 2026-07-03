<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable(['tenant_id', 'slug', 'settings', 'active'])]
class Setting extends Model
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'active' => 'boolean',
        ];
    }

    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('slug', 'like', $group.'.%');
    }

    public static function groupSlug(string $group, string $key): string
    {
        return $group.'.'.$key;
    }

    public static function forSlug(string $slug): ?self
    {
        $tenantId = currentTenantId();

        if (! $tenantId) {
            return null;
        }

        return static::query()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->first();
    }

    public static function saveForSlug(string $slug, array $settings, bool $active = true): self
    {
        $tenantId = currentTenantId();

        return static::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'slug' => $slug,
            ],
            [
                'settings' => $settings,
                'active' => $active,
            ]
        );
    }

    /**
     * @return Collection<string, self>
     */
    public static function forGroup(string $group): Collection
    {
        $tenantId = currentTenantId();

        if (! $tenantId) {
            return collect();
        }

        return static::query()
            ->where('tenant_id', $tenantId)
            ->group($group)
            ->get()
            ->keyBy(fn (self $setting) => str($setting->slug)->after($group.'.')->toString());
    }

    public const BLOG_SETTINGS_SLUG = 'blog-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function blogSettingsDefaults(): array
    {
        return [
            'section_title' => 'المدونة',
            'section_description' => 'مقالات وتدوينات متخصصة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function blogSettings(): array
    {
        $saved = static::forSlug(static::BLOG_SETTINGS_SLUG);

        return array_merge(static::blogSettingsDefaults(), $saved?->settings ?? []);
    }

    public const PORTFOLIO_SETTINGS_SLUG = 'portfolio-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function portfolioSettingsDefaults(): array
    {
        return [
            'section_title' => 'معرض الأعمال',
            'section_description' => 'عرض وإدارة مشاريعك وأعمالك السابقة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function portfolioSettings(): array
    {
        $saved = static::forSlug(static::PORTFOLIO_SETTINGS_SLUG);

        return array_merge(static::portfolioSettingsDefaults(), $saved?->settings ?? []);
    }

    public const STORE_SETTINGS_SLUG = 'store-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function storeSettingsDefaults(): array
    {
        return [
            'section_title' => 'المتجر',
            'section_description' => 'تسوق منتجاتنا المميزة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function storeSettings(): array
    {
        $saved = static::forSlug(static::STORE_SETTINGS_SLUG);

        return array_merge(static::storeSettingsDefaults(), $saved?->settings ?? []);
    }

    public const SERVICE_SETTINGS_SLUG = 'service-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function serviceSettingsDefaults(): array
    {
        return [
            'section_title' => 'الخدمات',
            'section_description' => 'احجز خدماتنا المتخصصة بسهولة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function serviceSettings(): array
    {
        $saved = static::forSlug(static::SERVICE_SETTINGS_SLUG);

        return array_merge(static::serviceSettingsDefaults(), $saved?->settings ?? []);
    }
}
