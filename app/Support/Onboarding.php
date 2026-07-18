<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Support\Collection;

class Onboarding
{
    /**
     * @return array{
     *     percentage: int,
     *     completed: int,
     *     total: int,
     *     current_step: string|null,
     *     steps: Collection<int, array{key: string, title: string, description: string, icon: string, done: bool, unlocked: bool}>,
     *     dismissed: bool
     * }
     */
    public function forTenant(?Tenant $tenant): array
    {
        if (! $tenant) {
            return [
                'percentage' => 0,
                'completed' => 0,
                'total' => 0,
                'current_step' => null,
                'steps' => collect(),
                'dismissed' => false,
            ];
        }

        $done = [
            'business' => $this->businessDone($tenant),
            'contact' => $this->contactDone($tenant),
            'identity' => $this->identityDone($tenant),
            'goal' => $this->goalDone($tenant),
            'catalog' => $this->catalogDone($tenant),
            'orders' => $this->ordersDone($tenant),
        ];

        $definitions = [
            'business' => ['معلومات النشاط', 'عرّف بنشاطك', 'hugeicons:store-02'],
            'contact' => ['معلومات الاتصال', 'كيف يوصلك عميلك؟', 'hugeicons:call'],
            'identity' => ['الهوية والألوان', 'إيش يميزك؟', 'hugeicons:paint-board'],
            'goal' => ['هدف الصفحة', 'إيش تبي من العميل؟', 'hugeicons:cursor-magic-selection-02'],
            'catalog' => ['جهّز كتالوجك', 'إيش تبيع؟', 'hugeicons:package'],
            'orders' => ['استقبل الطلبات', 'إعداد مرة واحدة، أتمتها وانساها.', 'hugeicons:invoice-03'],
        ];

        $unlocked = true;
        $steps = collect();

        foreach ($definitions as $key => [$title, $description, $icon]) {
            $steps->push([
                'key' => $key,
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
                'done' => $done[$key],
                'unlocked' => $unlocked,
            ]);

            if (! $done[$key]) {
                $unlocked = false;
            }
        }

        $total = $steps->count();
        $completed = $steps->where('done', true)->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
        $current = $steps->firstWhere('done', false);

        return [
            'percentage' => $percentage,
            'completed' => $completed,
            'total' => $total,
            'current_step' => $current['key'] ?? null,
            'steps' => $steps,
            'dismissed' => $this->isDismissed($tenant),
        ];
    }

    public function isDismissed(Tenant $tenant): bool
    {
        return filled(data_get($tenant->meta, 'onboarding_wizard_dismissed_at'));
    }

    public function dismiss(Tenant $tenant): void
    {
        $tenant->meta->set('onboarding_wizard_dismissed_at', now()->toIso8601String());
        $tenant->save();
    }

    public function businessDone(Tenant $tenant): bool
    {
        return filled($tenant->name)
            && filled(data_get($tenant->meta, 'industry'))
            && filled(app(TenantProfileService::class)->bio($tenant))
            && app(TenantProfileService::class)->hasLogo($tenant);
    }

    public function contactDone(Tenant $tenant): bool
    {
        $contact = app(TenantProfileService::class)->contact($tenant);

        return filled($contact['phone'])
            && filled($contact['email']);
    }

    public function identityDone(Tenant $tenant): bool
    {
        $tenant->loadMissing('theme');
        $themeId = $tenant->theme_id;

        if (! $themeId || blank($tenant->handle)) {
            return false;
        }

        $saved = $tenant->themeSettingsFor((int) $themeId);

        return filled(data_get($saved, 'primaryColor'));
    }

    public function goalDone(Tenant $tenant): bool
    {
        return filled(data_get($tenant->meta, 'primary_action_type'));
    }

    public function catalogDone(Tenant $tenant): bool
    {
        $enabled = data_get($tenant->config, 'enabled_content_types');

        if (! is_array($enabled)) {
            return false;
        }

        $sellableSlugs = app(ContentTypeRegistry::class)->configured()
            ->filter(fn (ContentType $contentType): bool => $contentType->sellable)
            ->pluck('slug');

        return collect($enabled)->contains(
            fn (mixed $slug): bool => is_string($slug) && $sellableSlugs->contains($slug)
        );
    }

    public function ordersDone(Tenant $tenant): bool
    {
        return $this->hasActivePayment($tenant) && $this->verificationDone($tenant);
    }

    public function hasActivePayment(Tenant $tenant): bool
    {
        setCurrentTenant($tenant);

        return app(PaymentMethodRegistry::class)->all()
            ->contains(fn (PaymentMethod $method): bool => (bool) data_get(Setting::paymentMethod($method->slug), 'active', false));
    }

    public function hasActiveShipping(Tenant $tenant): bool
    {
        setCurrentTenant($tenant);

        return app(ShippingMethodRegistry::class)->all()
            ->contains(fn (ShippingMethod $method): bool => (bool) data_get(Setting::shippingMethod($method->slug), 'active', false));
    }

    public function verificationDone(Tenant $tenant): bool
    {
        return filled(data_get($tenant->meta, 'identity_file'))
            || data_get($tenant->meta, 'confirm_status') === 'pending'
            || (bool) data_get($tenant->meta, 'is_confirmed');
    }
}
