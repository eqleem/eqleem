<div class="mt-4 rounded-xl border border-stone-200 bg-stone-50 p-4">
    <p class="text-sm font-semibold text-stone-800">
        {{ filled($selectedPaymentMethod['label'] ?? null) ? $selectedPaymentMethod['label'] : 'الدفع عند الاستلام' }}
    </p>

    @if (filled($selectedPaymentMethod['description'] ?? null))
        <p class="mt-2 text-sm text-stone-600">{{ $selectedPaymentMethod['description'] }}</p>
    @else
        <p class="mt-2 text-sm text-stone-600">ستدفع المبلغ نقداً عند استلام طلبك.</p>
    @endif

    @if ($shippingMethod === 'pickup' && $requiresShipping)
        <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
            الدفع عند الاستلام متاح عادةً للطلبات التي تتطلب شحناً. تأكد من أن هذا الخيار مناسب لطلبك.
        </p>
    @endif

    <button
        type="button"
        wire:click="confirmCashOnDelivery"
        wire:loading.attr="disabled"
        wire:target="confirmCashOnDelivery"
        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:opacity-70"
    >
        <span wire:loading.remove wire:target="confirmCashOnDelivery">تأكيد الطلب — الدفع عند الاستلام</span>
        <span wire:loading wire:target="confirmCashOnDelivery">جاري إنشاء الطلب...</span>
    </button>
</div>
