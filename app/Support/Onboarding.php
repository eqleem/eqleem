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
     *     steps: Collection<int, array{key: string, title: string, description: string, done: bool, unlocked: bool}>
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
            ];
        }

        $done = [
            'business' => $this->businessDone($tenant),
            'contact' => $this->contactDone($tenant),
            'identity' => $this->identityDone($tenant),
            'catalog' => $this->catalogDone($tenant),
            'orders' => $this->ordersDone($tenant),
        ];

        $definitions = [
            'business' => ['بيانات النشاط', 'مين أنت؟'],
            'contact' => ['بيانات الاتصال', 'كيف يوصل لك عميلك؟'],
            'identity' => ['الهوية والألوان', 'ايش يميزك؟'],
            'catalog' => ['الكتالوج', 'ايش تبيع؟'],
            'orders' => ['استقبل الطلبات', 'إعداد مرة واحدة، أتمتها وانساها.'],
        ];

        $unlocked = true;
        $steps = collect();

        foreach ($definitions as $key => [$title, $description]) {
            $steps->push([
                'key' => $key,
                'title' => $title,
                'description' => $description,
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
        ];
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
        $socialLinks = app(TenantProfileService::class)->socialLinks($tenant);

        return filled($contact['phone'])
            && filled($contact['email'])
            && filled($contact['whatsapp'])
            && filled($contact['country'])
            && filled($contact['city'])
            && $socialLinks->contains(fn (array $link): bool => filled($link['url'] ?? null));
    }

    public function identityDone(Tenant $tenant): bool
    {
        $tenant->loadMissing('theme');
        $themeId = $tenant->theme_id;

        if (! $themeId) {
            return false;
        }

        $saved = $tenant->themeSettingsFor((int) $themeId);

        return filled(data_get($saved, 'primaryColor'))
            && filled(data_get($saved, 'logoRadius'))
            && filled(data_get($saved, 'fontFamily'));
    }

    public function catalogDone(Tenant $tenant): bool
    {
        $enabled = data_get($tenant->config, 'enabled_content_types');

        return is_array($enabled) && count($enabled) > 0;
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
