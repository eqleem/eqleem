<ui:container>
    <div class="mb-6">
        <a href="{{ route('admin.plan.home') }}" class="inline-flex items-center gap-2 text-sm text-stone-500 hover:text-stone-800">
            <iconify-icon icon="hugeicons:arrow-right-01" class="text-lg rtl:rotate-180"></iconify-icon>
            العودة للباقات
        </a>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
        <h2 class="text-xl font-bold text-stone-900">إتمام الدفع — {{ $plan->label }}</h2>
        <p class="mt-2 text-sm text-stone-500">
            {{ $plan->formattedPrice() }} ر.س — {{ $plan->billingLabel() }}
        </p>

        @if (blank(config('services.moyasar.publishable_key')))
            <ui:alert class="mt-6" color="red">
                مفتاح Moyasar غير مُعرّف في ملف .env
            </ui:alert>
        @else
            <div id="moyasar-form" class="mt-6 min-h-[280px]"></div>
        @endif
    </div>
</ui:container>

@script
<script>
    Moyasar.init({
        element: '#moyasar-form',
        amount: @js($plan->price),
        currency: 'SAR',
        description: @js('اشتراك '.$plan->label.' — '.$plan->billingLabel()),
        publishable_api_key: @js(config('services.moyasar.publishable_key')),
        callback_url: @js(route('admin.payments.moyasar.callback')),
        methods: ['creditcard'],
        supported_networks: ['mada', 'visa', 'mastercard'],
        metadata: {
            plan_id: @js($plan->id),
            tenant_id: @js(currentTenant()?->id),
        },
    });
</script>
@endscript

<?php

use App\Models\Plan;

new class extends \Livewire\Component {
    public Plan $plan;

    public function mount(Plan $plan): void
    {
        abort_unless($plan->is_system && $plan->active && ! $plan->isFree(), 404);

        $this->plan = $plan;
    }

    public function render()
    {
        return $this->view()->layout('admin::layout')->title('إتمام الدفع');
    }
}; ?>
