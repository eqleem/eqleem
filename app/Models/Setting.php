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
        return static::sectionSettings(static::BLOG_SETTINGS_SLUG, static::blogSettingsDefaults());
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
        return static::sectionSettings(static::PORTFOLIO_SETTINGS_SLUG, static::portfolioSettingsDefaults());
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
        return static::sectionSettings(static::STORE_SETTINGS_SLUG, static::storeSettingsDefaults());
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
        return static::sectionSettings(static::SERVICE_SETTINGS_SLUG, static::serviceSettingsDefaults());
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
        return static::sectionSettings(static::DIGITAL_PRODUCT_SETTINGS_SLUG, static::digitalProductSettingsDefaults());
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
        return static::sectionSettings(static::DIGITAL_SERVICE_SETTINGS_SLUG, static::digitalServiceSettingsDefaults());
    }

    public const ON_DEMAND_SERVICE_SETTINGS_SLUG = 'on-demand-service-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function onDemandServiceSettingsDefaults(): array
    {
        return [
            'section_title' => 'خدمات حسب الطلب',
            'section_description' => 'خدمات مسعّرة حسب الوحدة مثل المتر والقطعة والساعة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function onDemandServiceSettings(): array
    {
        return static::sectionSettings(static::ON_DEMAND_SERVICE_SETTINGS_SLUG, static::onDemandServiceSettingsDefaults());
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
        return static::sectionSettings(static::MENU_SETTINGS_SLUG, static::menuSettingsDefaults());
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
        return static::sectionSettings(static::NEWSLETTER_SETTINGS_SLUG, static::newsletterSettingsDefaults());
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
        return static::sectionSettings(static::COURSE_SETTINGS_SLUG, static::courseSettingsDefaults());
    }

    public const UNIT_RENTAL_SETTINGS_SLUG = 'unit-rental-settings';

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function unitRentalSettingsDefaults(): array
    {
        return [
            'section_title' => 'تأجير الوحدات',
            'section_description' => 'احجز الوحدات المتاحة للتأجير بسهولة',
        ];
    }

    /**
     * @return array{section_title: string, section_description: string}
     */
    public static function unitRentalSettings(): array
    {
        return static::sectionSettings(static::UNIT_RENTAL_SETTINGS_SLUG, static::unitRentalSettingsDefaults());
    }

    public const REVIEW_SETTINGS_SLUG = 'review-settings';

    /**
     * @return array{section_title: string, per_page: int}
     */
    public static function reviewSettingsDefaults(): array
    {
        return [
            'section_title' => 'التقييمات',
            'per_page' => 12,
        ];
    }

    /**
     * @return array{section_title: string, per_page: int}
     */
    public static function reviewSettings(): array
    {
        $settings = static::sectionSettings(static::REVIEW_SETTINGS_SLUG, static::reviewSettingsDefaults());

        return [
            'section_title' => (string) ($settings['section_title'] ?? static::reviewSettingsDefaults()['section_title']),
            'per_page' => max(1, min(50, (int) ($settings['per_page'] ?? 12))),
        ];
    }

    /**
     * @param  array{section_title: string, section_description: string}  $defaults
     * @return array{section_title: string, section_description: string}
     */
    protected static function sectionSettings(string $slug, array $defaults): array
    {
        $saved = static::forSlug($slug);

        return array_merge($defaults, $saved?->settings ?? []);
    }

    public const LOCALE_CURRENCY_SETTINGS_SLUG = 'locale-currency';

    /**
     * @return array{default_language: string, default_currency: string, available_languages: list<string>, available_currencies: list<string>}
     */
    public static function localeCurrencySettingsDefaults(): array
    {
        return config('locales.defaults', [
            'default_language' => 'ar',
            'default_currency' => 'SAR',
            'available_languages' => ['ar'],
            'available_currencies' => ['SAR'],
        ]);
    }

    /**
     * @return array{default_language: string, default_currency: string, available_languages: list<string>, available_currencies: list<string>}
     */
    public static function localeCurrencySettings(): array
    {
        $saved = static::forSlug(static::LOCALE_CURRENCY_SETTINGS_SLUG);
        $defaults = static::localeCurrencySettingsDefaults();
        $settings = array_merge($defaults, $saved?->settings ?? []);

        $settings['available_languages'] = array_values(array_unique(array_filter(
            (array) data_get($settings, 'available_languages', $defaults['available_languages']),
            fn (mixed $code): bool => is_string($code) && $code !== '',
        )));

        $settings['available_currencies'] = array_values(array_unique(array_filter(
            (array) data_get($settings, 'available_currencies', $defaults['available_currencies']),
            fn (mixed $code): bool => is_string($code) && $code !== '',
        )));

        return $settings;
    }

    /**
     * @param  array{default_language: string, default_currency: string, available_languages: list<string>, available_currencies: list<string>}  $settings
     */
    public static function saveLocaleCurrencySettings(array $settings): self
    {
        return static::saveForSlug(static::LOCALE_CURRENCY_SETTINGS_SLUG, $settings);
    }

    public const PAYMENT_OPTIONS_GROUP = 'payment-options';

    /**
     * @return array<string, mixed>
     */
    public static function paymentMethodDefaults(string $slug): array
    {
        return config("payment-methods.{$slug}.defaults", []);
    }

    /**
     * @return array<string, mixed>
     */
    public static function paymentMethod(string $slug): array
    {
        $saved = static::forSlug(static::groupSlug(static::PAYMENT_OPTIONS_GROUP, $slug));
        $defaults = static::paymentMethodDefaults($slug);

        return array_merge($defaults, $saved?->settings ?? [], [
            'active' => (bool) data_get($saved, 'active', false),
        ]);
    }

    /**
     * @return Collection<string, self>
     */
    public static function forPaymentOptions(): Collection
    {
        return static::forGroup(static::PAYMENT_OPTIONS_GROUP);
    }

    public static function savePaymentMethod(string $slug, array $settings, bool $active = true): self
    {
        return static::saveForSlug(
            static::groupSlug(static::PAYMENT_OPTIONS_GROUP, $slug),
            $settings,
            $active,
        );
    }

    public const SHIPPING_OPTIONS_GROUP = 'shipping-options';

    public const CUSTOM_SHIPPING_SLUG = 'shipping-options.custom';

    /**
     * @return array<string, mixed>
     */
    public static function shippingMethodDefaults(string $slug): array
    {
        return config("shipping-methods.{$slug}.defaults", []);
    }

    /**
     * @return array<string, mixed>
     */
    public static function shippingMethod(string $slug): array
    {
        $saved = static::forSlug(static::groupSlug(static::SHIPPING_OPTIONS_GROUP, $slug));
        $defaults = static::shippingMethodDefaults($slug);

        return array_merge($defaults, $saved?->settings ?? [], [
            'active' => (bool) data_get($saved, 'active', false),
        ]);
    }

    /**
     * @return list<array{id: string, name: string, price: float, country: string, all_cities: bool, city_ids: list<string>, active: bool}>
     */
    public static function customShippingDefaults(): array
    {
        return [];
    }

    /**
     * @return list<array{id: string, name: string, price: float, country: string, all_cities: bool, city_ids: list<string>, active: bool}>
     */
    public static function customShippingOptions(): array
    {
        $saved = static::forSlug(static::CUSTOM_SHIPPING_SLUG);

        return array_values(data_get($saved?->settings, 'items', static::customShippingDefaults()));
    }

    public static function saveShippingMethod(string $slug, array $settings, bool $active = true): self
    {
        return static::saveForSlug(
            static::groupSlug(static::SHIPPING_OPTIONS_GROUP, $slug),
            $settings,
            $active,
        );
    }

    /**
     * @param  list<array{id: string, name: string, price: float, country: string, all_cities: bool, city_ids: list<string>, active: bool}>  $items
     */
    public static function saveCustomShippingOptions(array $items): self
    {
        return static::saveForSlug(static::CUSTOM_SHIPPING_SLUG, [
            'items' => array_values($items),
        ]);
    }
}
