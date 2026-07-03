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

    public const DIGITAL_PRODUCT_SETTINGS_SLUG = 'digital-product-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function digitalProductSettingsDefaults(): array
    {
        return [
            'section_title' => 'المنتجات الرقمية',
            'section_description' => 'منتجات رقمية قابلة للتحميل والوصول الفوري',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function digitalProductSettings(): array
    {
        $saved = static::forSlug(static::DIGITAL_PRODUCT_SETTINGS_SLUG);

        return array_merge(static::digitalProductSettingsDefaults(), $saved?->settings ?? []);
    }

    public const DIGITAL_SERVICE_SETTINGS_SLUG = 'digital-service-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function digitalServiceSettingsDefaults(): array
    {
        return [
            'section_title' => 'الخدمات الرقمية',
            'section_description' => 'خدمات رقمية احترافية مع مدة تسليم واضحة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function digitalServiceSettings(): array
    {
        $saved = static::forSlug(static::DIGITAL_SERVICE_SETTINGS_SLUG);

        return array_merge(static::digitalServiceSettingsDefaults(), $saved?->settings ?? []);
    }

    public const MENU_SETTINGS_SLUG = 'menu-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function menuSettingsDefaults(): array
    {
        return [
            'section_title' => 'قائمة الطعام',
            'section_description' => 'وجبات طازجة مع أحجام وإضافات متنوعة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function menuSettings(): array
    {
        $saved = static::forSlug(static::MENU_SETTINGS_SLUG);

        return array_merge(static::menuSettingsDefaults(), $saved?->settings ?? []);
    }

    public const NEWSLETTER_SETTINGS_SLUG = 'newsletter-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function newsletterSettingsDefaults(): array
    {
        return [
            'section_title' => 'النشرة البريدية',
            'section_description' => 'أحدث مقالات النشرة الأسبوعية ونشراتنا المتخصصة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function newsletterSettings(): array
    {
        $saved = static::forSlug(static::NEWSLETTER_SETTINGS_SLUG);

        return array_merge(static::newsletterSettingsDefaults(), $saved?->settings ?? []);
    }

    public const COURSE_SETTINGS_SLUG = 'course-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function courseSettingsDefaults(): array
    {
        return [
            'section_title' => 'الدورات التدريبية',
            'section_description' => 'دورات تعليمية عملية مع دروس وفصول منظمة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function courseSettings(): array
    {
        $saved = static::forSlug(static::COURSE_SETTINGS_SLUG);

        return array_merge(static::courseSettingsDefaults(), $saved?->settings ?? []);
    }
}
