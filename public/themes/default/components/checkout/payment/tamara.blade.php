<div class="mt-4 rounded-xl border border-stone-200 bg-stone-50 p-4">
    <p class="text-sm font-semibold text-stone-800">
        {{ filled($selectedPaymentMethod['label'] ?? null) ? $selectedPaymentMethod['label'] : 'تمارا' }}
    </p>

    @if (filled($selectedPaymentMethod['description'] ?? null))
        <p class="mt-2 text-sm text-stone-600">{{ $selectedPaymentMethod['description'] }}</p>
    @else
        <p class="mt-2 text-sm text-stone-600">قسّم مشترياتك على دفعات مريحة.</p>
    @endif

    @if (blank($selectedPaymentMethod['api_token'] ?? null))
        <p class="mt-3 text-sm text-amber-700">خدمة تمارا غير مكتملة الإعداد. يرجى اختيار وسيلة دفع أخرى.</p>
    @else
        <p class="mt-3 rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-800">
            سيتم توجيهك لإتمام الدفع عبر تمارا قريباً. حالياً يرجى استخدام وسيلة دفع أخرى.
        </p>
    @endif
</div>
