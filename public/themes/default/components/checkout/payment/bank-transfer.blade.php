@php
    $accounts = collect($selectedPaymentMethod['accounts'] ?? [])->filter(
        fn (array $account): bool => filled($account['bank_name'] ?? null) || filled($account['iban'] ?? null)
    );
@endphp

<div class="mt-4 rounded-xl border border-stone-200 bg-stone-50 p-4">
    <p class="text-sm font-semibold text-stone-800">تفاصيل التحويل البنكي</p>

    @if ($accounts->isEmpty())
        <p class="mt-2 text-sm text-amber-700">لا توجد حسابات بنكية متاحة حالياً. يرجى التواصل مع المتجر.</p>
    @else
        <p class="mt-1 text-xs text-stone-500">اختر الحساب الذي حوّلت إليه، ثم أدخل رقم الحوالة أو إيصال التحويل.</p>

        <div class="mt-4 space-y-3">
            @foreach ($accounts as $account)
                <label
                    wire:key="bank-account-{{ $account['id'] ?? $loop->index }}"
                    class="flex cursor-pointer gap-3 rounded-xl border border-stone-200 bg-white p-3 hover:border-primary-300"
                >
                    <input
                        wire:model="bankTransferAccountId"
                        type="radio"
                        value="{{ $account['id'] }}"
                        class="mt-1 h-4 w-4 shrink-0 border-stone-300 text-primary-600 focus:ring-primary-400"
                    >
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-stone-900">{{ $account['bank_name'] ?: 'حساب بنكي' }}</p>
                        <p class="text-xs text-stone-500">{{ $account['account_name'] ?? '' }}</p>
                        @if (filled($account['iban'] ?? null))
                            <p class="mt-1 text-xs text-stone-600" dir="ltr">{{ $account['iban'] }}</p>
                        @endif
                        @if (filled($account['account_number'] ?? null))
                            <p class="text-xs text-stone-500" dir="ltr">{{ $account['account_number'] }}</p>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>

        <div class="mt-4">
            <label class="mb-1 block text-sm text-stone-600">رقم الحوالة / إيصال التحويل</label>
            <input
                wire:model="bankTransferReference"
                type="text"
                dir="ltr"
                placeholder="مثال: TRX-123456"
                class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-stone-400"
            >
            @error('bankTransferReference') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('bankTransferAccountId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <button
            type="button"
            wire:click="confirmBankTransfer"
            wire:loading.attr="disabled"
            wire:target="confirmBankTransfer"
            @disabled($accounts->isEmpty())
            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="confirmBankTransfer">تأكيد التحويل وإتمام الطلب</span>
            <span wire:loading wire:target="confirmBankTransfer">جاري إنشاء الطلب...</span>
        </button>
    @endif
</div>
