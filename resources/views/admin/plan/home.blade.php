<ui:container class="!pb-24">
    <ui:mainbox title="إدارة الاشتراك" subtitle="اختر الباقة المناسبة لصفحتك.">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-6 h-6">
                <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path opacity=".4" d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </x-slot:icon>
    </ui:mainbox>

    @if (session('status'))
        <ui:alert class="mt-6" color="{{ session('color', 'green') }}">
            {{ session('status') }}
        </ui:alert>
    @endif

    <div class="mt-10 flex flex-col items-center gap-3">
        <div class="flex items-center">
            <div class="inline-flex rounded-xl bg-stone-300/60 p-1">
                <button type="button" wire:click="$set('billingPeriod', 'monthly')"
                    @class([
                        'rounded-lg px-6 py-2.5 text-sm font-semibold transition',
                        'bg-white text-stone-900 shadow-sm' => $billingPeriod === 'monthly',
                        'text-stone-500 hover:text-stone-800' => $billingPeriod !== 'monthly',
                    ])>
                    شهري
                </button>
                <button type="button" wire:click="$set('billingPeriod', 'yearly')"
                    @class([
                        'rounded-lg px-6 py-2.5 text-sm font-semibold transition',
                        'bg-white text-stone-900 shadow-sm' => $billingPeriod === 'yearly',
                        'text-stone-500 hover:text-stone-800' => $billingPeriod !== 'yearly',
                    ])>
                    سنوي
                </button>
            </div>

            <div class="pointer-events-none ms-1.5 flex flex-col items-start pt-1">
                <p class="-rotate-3 whitespace-nowrap ms-3 text-[10px] font-bold leading-none text-emerald-700">
                    خصم شهرين
                </p>
                <svg
                    viewBox="0 0 44 20"
                    class="h-4 w-9 text-emerald-500"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M2 12 C 10 8, 18 10, 28 12"
                        stroke="currentColor"
                        stroke-width="1.75"
                        stroke-linecap="round"
                        opacity="0.35"
                    />
                    <path
                        d="M2 12 C 10 8, 18 10, 28 12"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                    <path
                        d="M24 9 L30 12 L24 15"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>
        </div>
    </div>

    <div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($this->displayPlans() as $card)
            @php
                $wrapperClass = match (true) {
                    $card['highlighted'] => 'relative rounded-2xl bg-gradient-to-b from-orange-400 via-rose-500 to-violet-600 p-0.5 xshadow-sm',
                    $card['free'] => 'rounded-2xl bg-stone-100',
                    default => 'rounded-2xl bg-white ring-1 ring-stone-200',
                };

                $innerClass = match (true) {
                    $card['highlighted'] => 'flex h-full flex-col rounded-[calc(1rem-1px)] bg-white p-6',
                    default => 'flex h-full flex-col p-6',
                };
            @endphp

            <div @class([$wrapperClass, 'relative'])>
                <div @class([$innerClass])>
                    @if ($card['current'])
                        <span class="absolute -top-3 right-4 z-10 rounded-full bg-stone-900 px-3 py-1 text-xs font-medium text-white">
                            باقتك الحالية
                        </span>
                    @endif

                    @if ($card['featured'] ?? false)
                        <span class="absolute -top-3 left-4 z-10 rounded-full bg-amber-500 px-3 py-1 text-xs font-medium text-white">
                            الأوفر
                        </span>
                    @endif

                    {{-- Header --}}
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            @if ($card['free'])
                                <span class="inline-flex rounded-md border border-stone-900 bg-white px-2.5 py-1 text-sm font-bold text-stone-900">
                                    {{ $card['title'] }}
                                </span>
                            @else
                                <h3 class="text-lg font-bold text-stone-900">
                                    <span>{{ config('app.name') }}</span>
                                    <span class="{{ $card['accent_class'] }}">{{ $card['title'] }}</span>
                                </h3>
                            @endif
                        </div>

                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-600">
                            <iconify-icon icon="{{ $card['icon'] }}" class="text-xl"></iconify-icon>
                        </div>
                    </div>

                    {{-- Description --}}
                    <p class="mb-6 text-sm leading-relaxed text-stone-500">
                        {{ $card['description'] }}
                    </p>

                    {{-- Pricing --}}
                    <div class="mb-6">
                        @if ($card['free'])
                            <p class="text-4xl font-bold tracking-tight text-stone-900">مجاناً</p>
                            <p class="mt-1 text-sm text-stone-400">بدون حد زمني</p>
                        @else
                            <p class="text-4xl font-bold tracking-tight text-stone-900" >
                                {{ money_format($card['price']) }}
                            </p>
                            <p class="mt-1 text-sm text-stone-400">{{ $card['interval_label'] }}</p>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="mb-6">
                        @if ($card['current'])
                            <ui:button
                                class="h-11 w-full !rounded-lg"
                                variant="outline"
                                disabled
                                label="مفعّلة"
                            />
                        @elseif ($card['free'])
                            <ui:button
                                class="h-11 w-full !rounded-lg !border-stone-900 !bg-white !text-stone-900 hover:!bg-stone-50"
                                variant="outline"
                                wire:click="subscribeFree"
                                target="subscribeFree"
                                label="ابدأ مجاناً"
                            />
                        @else
                            <ui:button
                                class="h-11 w-full !rounded-lg !bg-stone-900 !text-white hover:!bg-stone-800"
                                href="{{ route('admin.plan.checkout', $card['plan_id']) }}"
                                label="اشترك الآن"
                            />
                        @endif
                    </div>

                    {{-- Divider --}}
                    <div class="mb-6 h-px bg-[repeating-linear-gradient(to_right,#d6d3d1_0,#d6d3d1_6px,transparent_6px,transparent_11px)]"></div>

                    {{-- Features --}}
                    <ul class="grow space-y-3">
                        @foreach ($card['features'] as $feature)
                            <li class="flex items-start gap-2.5 text-sm text-stone-700">
                                <iconify-icon icon="solar:check-circle-bold" class="mt-0.5 shrink-0 text-base text-stone-900"></iconify-icon>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Audience footer --}}
                    <div class="mt-8 border-t border-stone-200 pt-6">
                        <div class="flex items-start gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-stone-200 text-stone-600">
                                <iconify-icon icon="{{ $card['audience']['icon'] }}" class="text-lg"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-stone-900">{{ $card['audience']['title'] }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-stone-500">{{ $card['audience']['description'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <section class="mt-20">
        <div class="mx-auto max-w-3xl text-center">
            <div class="inline-flex size-11 items-center justify-center rounded-xl bg-stone-100 text-stone-600">
                <iconify-icon icon="solar:question-circle-bold" class="text-xl"></iconify-icon>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-stone-900">الأسئلة المتكررة</h2>
            <p class="mt-2 text-sm leading-relaxed text-stone-500">
                إجابات سريعة عن الاشتراكات والباقات. لم تجد إجابتك؟
                <a href="{{ route('admin.settings.home') }}" wire:navigate class="font-medium text-stone-700 underline underline-offset-2 hover:text-stone-900">تواصل معنا</a>.
            </p>
        </div>

        <div
            x-cloak
            x-data="{ active: 1 }"
            class="mx-auto mt-10 max-w-3xl overflow-hidden rounded-2xl bg-white ring-1 ring-stone-200"
        >
            @foreach ($this->subscriptionFaqs() as $index => $faq)
                <div
                    wire:key="plan-faq-{{ $index }}"
                    x-data="{
                        id: {{ $index + 1 }},
                        get expanded() {
                            return this.active === this.id
                        },
                        set expanded(value) {
                            this.active = value ? this.id : null
                        },
                    }"
                    class="@unless($loop->last) border-b border-stone-200 @endunless"
                    role="region"
                >
                    <h3>
                        <button
                            type="button"
                            @click="expanded = !expanded"
                            :aria-expanded="expanded"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start text-sm font-semibold text-stone-900 transition hover:bg-stone-50 sm:px-7"
                        >
                            <span>{{ $faq['question'] }}</span>
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-500 transition"
                                :class="expanded ? 'rotate-180' : ''"
                            >
                                <iconify-icon icon="solar:alt-arrow-down-linear" class="text-lg"></iconify-icon>
                            </span>
                        </button>
                    </h3>

                    <div x-show="expanded" x-collapse>
                        <div class="border-t border-stone-100 px-6 pb-6 pt-4 sm:px-7">
                            <p class="text-sm leading-relaxed text-stone-600">{{ $faq['answer'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</ui:container>

<?php

use App\Actions\SubscribeTenantToPlan;
use App\Models\Plan;

new class extends \Livewire\Component {
    public string $billingPeriod = 'monthly';

    public function mount(): void
    {
        if ($this->billingPeriod === 'monthly' && request()->boolean('yearly')) {
            $this->billingPeriod = 'yearly';
        }
    }

    /**
     * @return list<array{
     *     title: string,
     *     description: string,
     *     price: int,
     *     free: bool,
     *     current: bool,
     *     featured: bool,
     *     highlighted: bool,
     *     interval_label: string,
     *     plan_id: int|null,
     *     tier: string,
     *     accent_class: string,
     *     icon: string,
     *     features: list<string>,
     *     audience: array{title: string, description: string, icon: string}
     * }>
     */
    public function displayPlans(): array
    {
        $plans = Plan::query()->system()->get()->keyBy('slug');
        $currentPlanId = currentTenant()?->subscription?->plan_id;

        $free = $plans->get('free');
        $basic = $plans->get('basic-'.$this->billingPeriod);
        $pro = $plans->get('pro-'.$this->billingPeriod);

        return array_values(array_filter([
            $this->planCard($free, $currentPlanId, free: true),
            $this->planCard($basic, $currentPlanId, featured: $this->billingPeriod === 'yearly'),
            $this->planCard($pro, $currentPlanId),
        ]));
    }

    protected function planCard(?Plan $plan, ?int $currentPlanId, bool $free = false, bool $featured = false): ?array
    {
        if (! $plan) {
            return null;
        }

        $tier = $free ? 'free' : (string) data_get($plan->meta, 'tier', 'basic');

        return [
            'plan_id' => $plan->id,
            'title' => $plan->label,
            'description' => (string) data_get($plan->meta, 'description', ''),
            'price' => $plan->price,
            'free' => $free,
            'current' => $currentPlanId === $plan->id,
            'featured' => $featured && ! $free,
            'highlighted' => $tier === 'pro',
            'interval_label' => $plan->billingLabel(),
            'tier' => $tier,
            'accent_class' => $this->tierAccentClass($tier),
            'icon' => $this->tierIcon($tier),
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

    protected function tierIcon(string $tier): string
    {
        return match ($tier) {
            'pro' => 'solar:user-bold',
            default => 'solar:user-rounded-bold',
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
     * @return array{title: string, description: string, icon: string}
     */
    protected function tierAudience(string $tier): array
    {
        return match ($tier) {
            'free' => [
                'title' => 'للمبتدئين',
                'description' => 'مثالية لمن يريد تجربة المنصة وإنشاء صفحته الأولى بسرعة.',
                'icon' => 'solar:pen-new-square-bold',
            ],
            'basic' => [
                'title' => 'للمشاريع الشخصية',
                'description' => 'مناسبة للصفحات الشخصية والمشاريع الصغيرة التي تحتاج مزايا أكثر.',
                'icon' => 'solar:laptop-minimalistic-bold',
            ],
            'pro' => [
                'title' => 'للأعمال الاحترافية',
                'description' => 'الخيار الأمثل للأعمال والصفحات التي تحتاج أدوات متقدمة ودعماً مباشراً.',
                'icon' => 'solar:case-round-bold',
            ],
            default => [
                'title' => '',
                'description' => '',
                'icon' => 'solar:user-rounded-bold',
            ],
        };
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

    public function subscribeFree(): void
    {
        $tenant = currentTenant();
        $plan = Plan::query()->where('slug', 'free')->where('is_system', true)->first();

        if (! $tenant || ! $plan) {
            session()->flash('color', 'red');
            session()->flash('status', 'تعذّر تفعيل الباقة المجانية.');

            return;
        }

        SubscribeTenantToPlan::run($tenant, $plan);

        session()->flash('status', 'تم تفعيل الباقة المجانية.');
    }

    public function render()
    {
        return $this->view()->layout('admin::layout')->title('الاشتراك');
    }
}; ?>
