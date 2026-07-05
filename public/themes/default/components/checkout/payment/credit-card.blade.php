<div class="mt-4 rounded-xl border border-stone-200 bg-stone-50 p-4">
    <p class="text-sm font-semibold text-stone-800">
        {{ filled($selectedPaymentMethod['label'] ?? null) ? $selectedPaymentMethod['label'] : 'البطاقة الائتمانية' }}
    </p>

    @if (filled($selectedPaymentMethod['description'] ?? null))
        <p class="mt-1 text-xs text-stone-500">{{ $selectedPaymentMethod['description'] }}</p>
    @endif

    @if (blank(config('services.moyasar.publishable_key')))
        <p class="mt-3 text-sm text-amber-700">بوابة الدفع غير مهيأة حالياً. يرجى اختيار وسيلة دفع أخرى.</p>
    @else
        @if (! $creditCardReady)
            <p class="mt-3 text-xs text-stone-500">بعد التحقق من بياناتك، ستظهر بوابة الدفع الآمنة لإتمام عملية الدفع.</p>

            <button
                type="button"
                wire:click="prepareCreditCardPayment"
                wire:loading.attr="disabled"
                wire:target="prepareCreditCardPayment"
                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:opacity-70"
            >
                <span wire:loading.remove wire:target="prepareCreditCardPayment">متابعة للدفع بالبطاقة</span>
                <span wire:loading wire:target="prepareCreditCardPayment">جاري التحقق...</span>
            </button>
        @else
            <div wire:ignore id="store-moyasar-form" class="mt-4 min-h-[280px] rounded-xl bg-white p-2"></div>
        @endif
    @endif

    @error('paymentMethod') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
</div>

@push('scripts')
    @if ($creditCardReady && filled(config('services.moyasar.publishable_key')))
        <link rel="stylesheet" href="{{ asset('assets/vendor/moyasar/moyasar.css') }}">
        <script src="{{ asset('assets/vendor/moyasar/moyasar.js') }}"></script>
    @endif
@endpush

@script
<script>
    const initStoreMoyasar = (amount) => {
        const element = document.getElementById('store-moyasar-form');

        if (! element || typeof Moyasar === 'undefined') {
            return;
        }

        element.innerHTML = '';

        Moyasar.init({
            element: '#store-moyasar-form',
            amount: amount,
            currency: @js(money_currency()),
            description: @js('طلب متجر — '.(tenant()?->name ?? '')),
            publishable_api_key: @js(config('services.moyasar.publishable_key')),
            callback_url: @js(route('tenant.payments.moyasar.callback', ['tenant' => tenant()?->handle])),
            methods: ['creditcard'],
            supported_networks: ['mada', 'visa', 'mastercard'],
            metadata: {
                tenant_id: @js(currentTenantId()),
                source: 'store_checkout',
            },
        });
    };

    $wire.on('init-store-moyasar', ({ amount }) => {
        setTimeout(() => initStoreMoyasar(amount), 100);
    });

    if (@js($creditCardReady)) {
        initStoreMoyasar(@js($grandTotal));
    }
</script>
@endscript
