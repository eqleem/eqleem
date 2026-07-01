<?php

namespace App\Support;

use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Collection;

class PageCompletion
{
    /**
     * @return array{
     *     percentage: int,
     *     completed: int,
     *     total: int,
     *     steps: Collection<int, array{key: string, label: string, hint: string, done: bool, url: string}>
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

        $locale = app()->getLocale();
        $steps = collect([
            $this->step(
                key: 'logo',
                label: 'شعار الصفحة',
                hint: 'ارفع شعاراً يميّز علامتك ويظهر في أعلى صفحتك.',
                done: filled(data_get($tenant->meta, 'logo')) || filled(data_get($tenant->data, 'logo')),
                url: route('admin.settings.detail', 'general-info'),
            ),
            $this->step(
                key: 'slogan',
                label: 'الشعار النصّي',
                hint: 'أضف جملة قصيرة تعبّر عن نشاطك وتجذب الزوار.',
                done: filled(data_get($tenant->meta, "slogan.{$locale}")) || filled(data_get($tenant->meta, 'slogan')),
                url: route('admin.settings.detail', 'general-info'),
            ),
            $this->step(
                key: 'theme',
                label: 'قالب التصميم',
                hint: 'اختر قالباً يناسب هويتك ويُبرز محتواك.',
                done: filled($tenant->theme_id),
                url: route('admin.page.home', ['tab' => 'design']),
            ),
            $this->step(
                key: 'blocks',
                label: 'أقسام الصفحة',
                hint: 'أضف بلوكاً واحداً على الأقل لعرض خدماتك أو منتجاتك.',
                done: Block::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('parent_id')
                    ->where('is_default', false)
                    ->where('active', true)
                    ->exists(),
                url: route('admin.page.home', ['tab' => 'structure']),
            ),
            $this->step(
                key: 'contact',
                label: 'بيانات التواصل',
                hint: 'أضف رقم هاتف أو بريداً إلكترونياً ليتمكن العملاء من الوصول إليك.',
                done: filled($tenant->phone) || filled($tenant->email),
                url: route('admin.settings.detail', 'general-info'),
            ),
            $this->step(
                key: 'content',
                label: 'محتوى منشور',
                hint: 'انشر أول مقال أو منتج ليظهر نشاطك للزوار.',
                done: Content::query()
                    ->withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('status', 'published')
                    ->exists(),
                url: route('admin.page.home', ['tab' => 'blog']),
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
     * @return array{key: string, label: string, hint: string, done: bool, url: string}
     */
    protected function step(string $key, string $label, string $hint, bool $done, string $url): array
    {
        return compact('key', 'label', 'hint', 'done', 'url');
    }
}
