<x-tenant::pages.layout>
    <section class="mb-6">
        <x-tenant::breadcrumb :links="[['url' => null, 'title' => 'الباقات والأسعار']]" />
    </section>

    <section class="px-2 pb-8 pt-4" x-data="{ yearly: false }">
        <div class="mx-auto max-w-6xl">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-black leading-tight text-stone-900 sm:text-4xl lg:text-5xl">
                    جرّب الباقات لمدة 30 يوما مجانا بدون بطاقة بنكية
                </h1>
            </div>

            <div class="mt-8 inline-flex items-center gap-2 rounded-xl bg-stone-200 p-1">
                <button
                    type="button"
                    x-on:click="yearly = false"
                    x-bind:class="yearly ? 'text-stone-500' : 'bg-white'"
                    class="rounded-lg px-4 py-2 text-xs font-bold uppercase tracking-wider transition"
                >
                    شهري
                </button>
                <button
                    type="button"
                    x-on:click="yearly = true"
                    x-bind:class="yearly ? 'bg-white' : 'text-stone-500'"
                    class="rounded-lg px-4 py-2 text-xs font-bold uppercase tracking-wider transition"
                >
                    سنوي
                </button>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-5 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    <article
                        wire:key="pricing-plan-{{ $plan['slug'] }}"
                        class="rounded-2xl bg-stone-100 shadow-sm p-5"
                        @class([
                            'bg-white' => ! $plan['featured'],
                            'bg-gradient-to-br from-primary-500 to-primary-900 text-white shadow-lg border border-primary-500' => $plan['featured'],
                        ])
                    >
                        <h2 class="text-xl font-bold">{{ $plan['name'] }}</h2>
                        <p
                            @class([
                                'mt-2 text-sm leading-6 text-stone-500' => ! $plan['featured'],
                                'mt-2 text-sm leading-6 xtext-white/80' => $plan['featured'],
                            ])
                        >
                            {{ $plan['description'] }}
                        </p>

                        <div class="mt-6 min-h-16">
                            @if ($plan['is_custom'])
                                <p class="text-4xl font-black tracking-tight">حسب الطلب</p>
                            @else
                                <div class="flex items-end gap-1">
                                    <p
                                        @class([
                                            'text-5xl font-black tracking-tight text-stone-900' => ! $plan['featured'],
                                            'text-5xl font-black tracking-tight xtext-white' => $plan['featured'],
                                        ])
                                        x-text="yearly ? '{{ $plan['yearly_price'] }}' : '{{ $plan['monthly_price'] }}'"
                                    ></p>
                                    <span
                                        @class([
                                            'mb-1 text-lg text-stone-500' => ! $plan['featured'],
                                            'mb-1 text-lg text-white/75' => $plan['featured'],
                                        ])
                                        x-text="yearly ? '/سنويا' : '/شهريا'"
                                    ></span>
                                </div>
                            @endif
                        </div>

                        <div class="my-6 h-px bg-[repeating-linear-gradient(to_right,#cbd5e1_0,#cbd5e1_6px,transparent_6px,transparent_11px)]"></div>

                        <button
                            type="button"
                           
                            @class([
                                'inline-flex w-full items-center justify-center rounded-xl bg-primary-100 text-primary-800 hover:bg-primary-200 px-4 py-3 text-sm font-bold transition' => ! $plan['featured'],
                                'inline-flex w-full items-center justify-center rounded-xl bg-primary-50 text-primary-800 hover:bg-primary-200 px-4 py-3 text-sm font-bold transition' => $plan['featured'],
                            ])
                            x-text="yearly && '{{ $plan['yearly_cta'] }}' !== '' ? '{{ $plan['yearly_cta'] }}' : '{{ $plan['cta'] }}'"
                        >
                            {{ $plan['cta'] }}
                        </button>

                        <ul class="space-y-2 mt-8">
                            @foreach ($plan['features'] as $feature)
                                <li class="flex items-center gap-2 text-sm">
                                    <iconify-icon icon="solar:check-circle-bold" class="text-base"></iconify-icon>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</x-tenant::pages.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $plans = [];

    public function mount(): void
    {
        $this->plans = [
            [
                'slug' => 'startup',
                'name' => 'البداية',
                'description' => 'مناسبة للمشاريع الصغيرة التي تريد الانطلاق بسرعة.',
                'monthly_price' => 'مجانا',
                'yearly_price' => 'مجانا',
                'is_custom' => false,
                'featured' => false,
                'cta' => 'ابدأ مجانا',
                'yearly_cta' => 'ابدأ مجانا',
                'features' => ['حتى 10 أعضاء', 'حساب مشرف واحد', 'مساحة تخزين 5 جيجابايت'],
            ],
            [
                'slug' => 'pro',
                'name' => 'الاحترافية',
                'description' => 'مناسبة للأعمال المتنامية التي تحتاج مزايا أوسع.',
                'monthly_price' => '$99',
                'yearly_price' => '$79',
                'is_custom' => false,
                'featured' => true,
                'cta' => 'ابدأ تجربة 30 يوما',
                'yearly_cta' => 'وفر مع الخطة السنوية',
                'features' => ['أعضاء غير محدودين', 'حسابات مشرف غير محدودة', 'مساحة 500 جيجابايت', 'دومين مخصص', 'وصول API'],
            ],
             
            [
                'slug' => 'enterprise',
                'name' => 'المؤسسات',
                'description' => 'حلول متقدمة للشركات الكبيرة واحتياجات التوسع.',
                'monthly_price' => '',
                'yearly_price' => '',
                'is_custom' => true,
                'featured' => false,
                'cta' => 'تواصل للمزيد',
                'yearly_cta' => 'تواصل للمزيد',
                'features' => ['أعضاء غير محدودين', 'حسابات مشرف غير محدودة', 'حتى 5 تيرابايت تخزين', 'دومين مخصص', 'وصول API', 'كل التكاملات', 'كل الويدجت', 'دعم محادثة مباشرة', 'استيراد جماعي'],
            ],
        ];
    }
};
?>
