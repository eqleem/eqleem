<div class="mt-4 rounded-xl border border-stone-200 bg-stone-50 p-4">
    <p class="text-sm font-semibold text-stone-800">
        {{ filled($selectedPaymentMethod['label'] ?? null) ? $selectedPaymentMethod['label'] : 'وسيلة دفع مخصصة' }}
    </p>

    @if (filled($selectedPaymentMethod['description'] ?? null))
        <p class="mt-2 text-sm text-stone-600">{{ $selectedPaymentMethod['description'] }}</p>
    @endif

    @if (filled($selectedPaymentMethod['instructions'] ?? null))
        <div class="mt-3 rounded-lg border border-stone-200 bg-white p-3 text-sm text-stone-700 whitespace-pre-line">
            {{ $selectedPaymentMethod['instructions'] }}
        </div>
    @endif

    <div class="mt-4">
        <label class="mb-1 block text-sm text-stone-600">ملاحظة الدفع / رقم المرجع</label>
        <textarea
            wire:model="paymentNote"
            rows="3"
            placeholder="اكتب تفاصيل الدفع أو رقم المرجع بعد إتمام العملية"
            class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-stone-400"
        ></textarea>
        @error('paymentNote') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <button
        type="button"
        wire:click="confirmCustomPayment"
        wire:loading.attr="disabled"
        wire:target="confirmCustomPayment"
        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:opacity-70"
    >
        <span wire:loading.remove wire:target="confirmCustomPayment">تأكيد الدفع وإتمام الطلب</span>
        <span wire:loading wire:target="confirmCustomPayment">جاري إنشاء الطلب...</span>
    </button>
</div>
