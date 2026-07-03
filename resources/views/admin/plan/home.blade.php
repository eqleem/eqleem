<ui:container>
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

    <div class="mt-8 flex flex-col items-center gap-4">
        <p class="text-sm text-stone-500">دورة الفوترة</p>
        <div class="inline-flex rounded-xl bg-white p-1 shadow-sm ring-1 ring-stone-200">
            <button type="button" wire:click="$set('billingPeriod', 'monthly')"
                @class([
                    'rounded-lg px-5 py-2 text-sm font-medium transition',
                    'bg-stone-900 text-white' => $billingPeriod === 'monthly',
                    'text-stone-600 hover:text-stone-900' => $billingPeriod !== 'monthly',
                ])>
                شهري
            </button>
            <button type="button" wire:click="$set('billingPeriod', 'yearly')"
                @class([
                    'rounded-lg px-5 py-2 text-sm font-medium transition',
                    'bg-stone-900 text-white' => $billingPeriod === 'yearly',
                    'text-stone-600 hover:text-stone-900' => $billingPeriod !== 'yearly',
                ])>
                سنوي
            </button>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        @foreach ($this->displayPlans() as $card)
            <div @class([
                'relative flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 transition',
                'ring-stone-900 shadow-md' => $card['current'],
                'ring-stone-200' => ! $card['current'],
            ])>
                @if ($card['current'])
                    <span class="absolute -top-3 right-4 rounded-full bg-stone-900 px-3 py-1 text-xs font-medium text-white">
                        باقتك الحالية
                    </span>
                @endif

                @if ($card['featured'] ?? false)
                    <span class="absolute -top-3 left-4 rounded-full bg-amber-500 px-3 py-1 text-xs font-medium text-white">
                        الأوفر
                    </span>
                @endif

                <div class="mb-4">
                    <h3 class="text-xl font-bold text-stone-900">{{ $card['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-500">{{ $card['description'] }}</p>
                </div>

                <div class="mb-6">
                    @if ($card['free'])
                        <p class="text-4xl font-bold text-stone-900">مجاناً</p>
                        <p class="mt-1 text-sm text-stone-400">بدون حد زمني</p>
                    @else
                        <p class="text-4xl font-bold text-stone-900">
                            {{ \App\Support\Money::format($card['price']) }}
                            <span class="text-base font-medium text-stone-500">ر.س</span>
                        </p>
                        <p class="mt-1 text-sm text-stone-400">{{ $card['interval_label'] }}</p>
                    @endif
                </div>

                <div class="mt-auto">
                    @if ($card['current'])
                        <ui:button class="w-full" variant="outline" disabled label="مفعّلة" />
                    @elseif ($card['free'])
                        <ui:button class="w-full" wire:click="subscribeFree" target="subscribeFree" label="تفعيل الباقة المجانية" />
                    @else
                        <ui:button
                            class="w-full"
                            href="{{ route('admin.plan.checkout', $card['plan_id']) }}"
                            label="اشترك الآن"
                        />
                    @endif
                </div>
            </div>
        @endforeach
    </div>
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
     * @return list<array{title: string, description: string, price: int, free: bool, current: bool, featured: bool, interval_label: string, plan_id: int|null}>
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

        return [
            'plan_id' => $plan->id,
            'title' => $plan->label,
            'description' => (string) data_get($plan->meta, 'description', ''),
            'price' => $plan->price,
            'free' => $free,
            'current' => $currentPlanId === $plan->id,
            'featured' => $featured && ! $free,
            'interval_label' => $plan->billingLabel(),
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
