<?php

namespace App\Support;

use App\Models\Plan;
use Illuminate\Support\Collection;

class PlanCatalog
{
    /**
     * @return list<array{
     *     id: int,
     *     title: string,
     *     description: string,
     *     price: int,
     *     price_formatted: string,
     *     free: bool,
     *     current: bool,
     *     featured: bool,
     *     highlighted: bool,
     *     interval_label: string,
     *     tier: string,
     *     accent_class: string,
     *     features: list<string>,
     *     audience: array{title: string, description: string}
     * }>
     */
    public function displayPlans(string $billingPeriod, ?int $currentPlanId): array
    {
        $plans = Plan::query()->system()->get()->keyBy('slug');

        $free = $plans->get('free');
        $basic = $plans->get('basic-'.$billingPeriod);
        $pro = $plans->get('pro-'.$billingPeriod);

        return array_values(array_filter([
            $this->planCard($free, $currentPlanId, free: true),
            $this->planCard($basic, $currentPlanId, featured: $billingPeriod === 'yearly'),
            $this->planCard($pro, $currentPlanId),
        ]));
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function subscriptionFaqs(): array
    {
        return [
            [
                'question' => 'هل الباقة المجانية مجانية فعلاً؟',
                'answer' => 'نعم، يمكنك إنشاء صفحتك واستخدام الميزات الأساسية بدون بطاقة بنكية أو حد زمني. الباقة المجانية مناسبة للبدء وتجربة المنصة.',
            ],
            [
                'question' => 'ما الفرق بين الاشتراك الشهري والسنوي؟',
                'answer' => 'كلاهما يمنحك نفس الميزات، لكن الاشتراك السنوي يوفر خصم شهرين مقارنة بالدفع الشهري على مدار العام.',
            ],
            [
                'question' => 'هل يمكنني الترقية أو تخفيض باقتي لاحقاً؟',
                'answer' => 'نعم، يمكنك تغيير باقتك في أي وقت من صفحة الاشتراك. عند الترقية تُفعَّل الميزات الجديدة فوراً، وعند التخفيض تبقى الميزات الحالية حتى نهاية فترة الاشتراك.',
            ],
            [
                'question' => 'هل أحتاج بطاقة بنكية للباقة المجانية؟',
                'answer' => 'لا، تفعيل الباقة المجانية لا يتطلب أي بيانات دفع. بطاقة البنك مطلوبة فقط عند الاشتراك في الباقات المدفوعة.',
            ],
            [
                'question' => 'كيف يتم الدفع للباقات المدفوعة؟',
                'answer' => 'ندعم الدفع الآمن عبر بطاقات الائتمان والخصم (مدى، فيزا، ماستركارد). يتم تجديد الاشتراك تلقائياً في نهاية كل فترة ما لم تُلغِ الاشتراك.',
            ],
            [
                'question' => 'هل يمكنني إلغاء الاشتراك؟',
                'answer' => 'نعم، يمكنك إلغاء الاشتراك في أي وقت. ستبقى باقتك المدفوعة فعّالة حتى نهاية الفترة المدفوعة، ثم تعود صفحتك للباقة المجانية ما لم تجدّد.',
            ],
        ];
    }

    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     description: string,
     *     price: int,
     *     price_formatted: string,
     *     free: bool,
     *     current: bool,
     *     featured: bool,
     *     highlighted: bool,
     *     interval_label: string,
     *     tier: string,
     *     accent_class: string,
     *     features: list<string>,
     *     audience: array{title: string, description: string}
     * }|null
     */
    protected function planCard(?Plan $plan, ?int $currentPlanId, bool $free = false, bool $featured = false): ?array
    {
        if (! $plan) {
            return null;
        }

        $tier = $free ? 'free' : (string) data_get($plan->meta, 'tier', 'basic');

        return [
            'id' => $plan->id,
            'title' => __((string) $plan->name),
            'description' => (string) data_get($plan->meta, 'description', ''),
            'price' => $plan->price,
            'price_formatted' => $plan->formattedPrice(),
            'free' => $free,
            'current' => $currentPlanId === $plan->id,
            'featured' => $featured && ! $free,
            'highlighted' => $tier === 'pro',
            'interval_label' => $plan->billingLabel(),
            'tier' => $tier,
            'accent_class' => $this->tierAccentClass($tier),
            'features' => $this->tierFeatures($tier),
            'audience' => $this->tierAudience($tier),
        ];
    }

    protected function tierAccentClass(string $tier): string
    {
        return match ($tier) {
            'basic' => 'text-rose-500',
            'pro' => 'text-orange-500',
            default => 'text-stone-900',
        };
    }

    /**
     * @return list<string>
     */
    protected function tierFeatures(string $tier): array
    {
        return match ($tier) {
            'free' => [
                'صفحة شخصية واحدة',
                'قوالب أساسية جاهزة',
                'تحليلات أساسية',
                'تخصيص الألوان والخطوط',
                'دعم عبر البريد',
                'بدون بطاقة بنكية',
            ],
            'basic' => [
                'كل ميزات الباقة المجانية',
                'نطاق مخصص',
                'قوالب احترافية',
                'تحليلات متقدمة',

                'دعم أولوي',
            ],
            'pro' => [
                'كل ميزات بيسك',
                'صفحات غير محدودة',
                'تكاملات API',
                'أتمتة متقدمة',
                'تقارير مخصصة',
                'دعم مباشر',
            ],
            default => [],
        };
    }

    /**
     * @return array{title: string, description: string}
     */
    protected function tierAudience(string $tier): array
    {
        return match ($tier) {
            'free' => [
                'title' => 'للمبتدئين',
                'description' => 'مثالية لمن يريد تجربة المنصة وإنشاء صفحته الأولى بسرعة.',
            ],
            'basic' => [
                'title' => 'للمشاريع الشخصية',
                'description' => 'مناسبة للصفحات الشخصية والمشاريع الصغيرة التي تحتاج مزايا أكثر.',
            ],
            'pro' => [
                'title' => 'للأعمال الاحترافية',
                'description' => 'الخيار الأمثل للأعمال والصفحات التي تحتاج أدوات متقدمة ودعماً مباشراً.',
            ],
            default => [
                'title' => '',
                'description' => '',
            ],
        };
    }

    /**
     * @param  Collection<int, Plan>  $plans
     */
    public function findCheckoutPlan(Collection $plans, int $planId): ?Plan
    {
        $plan = $plans->firstWhere('id', $planId);

        if (! $plan instanceof Plan) {
            return null;
        }

        if (! $plan->is_system || ! $plan->active || $plan->isFree()) {
            return null;
        }

        return $plan;
    }
}
