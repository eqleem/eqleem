<?php

namespace App\Support;

use App\Models\Content;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Support\Collection;

class PageCompletion
{
    /**
     * @return array{
     *     percentage: int,
     *     completed: int,
     *     total: int,
     *     steps: Collection<int, array{key: string, label: string, hint: string, done: bool, modal: string}>
     * }
     */
    public function forTenant(?Tenant $tenant): array
    {
        if (! $tenant) {
            return [
                'percentage' => 0,
                'completed' => 0,
                'total' => 0,
                'steps' => collect(),
            ];
        }

        $profile = app(TenantProfileService::class);
        $contact = $profile->contact($tenant);
        $socialLinks = $profile->socialLinks($tenant);

        $steps = collect([
            $this->step(
                key: 'basic-info',
                label: 'البيانات الأساسية',
                hint: 'أضف اسم الصفحة وشعارها ونبذة تعريفية.',
                done: filled($tenant->name)
                    && $profile->hasLogo($tenant)
                    && filled($profile->bio($tenant)),
                modal: 'home-step-basic-info',
            ),
            $this->step(
                key: 'contact',
                label: 'بيانات الاتصال',
                hint: 'أضف رقم الجوال والبريد الإلكتروني والموقع.',
                done: filled($contact['phone'])
                    && filled($contact['email'])
                    && filled($contact['country'])
                    && filled($contact['city']),
                modal: 'home-step-contact',
            ),
            $this->step(
                key: 'social',
                label: 'السوشال ميديا',
                hint: 'أضف حساباً واحداً على الأقل في منصات التواصل.',
                done: $socialLinks->contains(fn (array $link): bool => filled($link['url'] ?? null)),
                modal: 'home-step-social',
            ),
            $this->step(
                key: 'content',
                label: 'إضافة محتوى',
                hint: 'انشر منتجاً أو تدوينة أو دورة ليظهر نشاطك.',
                done: Content::query()
                    ->withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('status', 'published')
                    ->where('type', '!=', 'social-link')
                    ->exists(),
                modal: 'home-step-content',
            ),
            $this->step(
                key: 'verification',
                label: 'التوثيق',
                hint: 'ارفع مستندات التوثيق الرسمية لحسابك.',
                done: filled(data_get($tenant->meta, 'identity_file'))
                    || data_get($tenant->meta, 'confirm_status') === 'pending'
                    || (bool) data_get($tenant->meta, 'is_confirmed'),
                modal: 'home-step-verification',
            ),
        ]);

        $total = $steps->count();
        $completed = $steps->where('done', true)->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'percentage' => $percentage,
            'completed' => $completed,
            'total' => $total,
            'steps' => $steps,
        ];
    }

    /**
     * @return array{key: string, label: string, hint: string, done: bool, modal: string}
     */
    protected function step(string $key, string $label, string $hint, bool $done, string $modal): array
    {
        return compact('key', 'label', 'hint', 'done', 'modal');
    }
}
