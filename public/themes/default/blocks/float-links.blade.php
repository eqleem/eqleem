<div>
    @if ($showWhatsappButton)
        <div class="{{ $width }} mx-auto relative flex justify-end">
            <div class="fixed bottom-0 ml-4 lg:-ml-20  mb-7 !z-50">
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="واتساب" class="flex size-14 items-center justify-center rounded-full bg-green-500 text-white shadow-lg transition hover:bg-green-600">
                    <iconify-icon icon="hugeicons:whatsapp" class="text-3xl" stroke-width="1.5"></iconify-icon>
                </a>
            </div>
        </div>
    @endif

    <div class="fixed bottom-4 {{ $positionClass }} z-40 flex flex-col items-center gap-3">
        @if ($showPhoneButton)
            <a href="tel:{{ $phoneNumber }}" aria-label="اتصال" class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg transition hover:bg-primary-700">
                <iconify-icon icon="hugeicons:call-02" class="text-3xl" stroke-width="1.5"></iconify-icon>
            </a>
        @endif
    </div>
</div>