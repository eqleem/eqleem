@php
    $data = $block->data ?? [];
    $position = $data['position'] ?? 'bottom-end';
    $showWhatsapp = (bool) ($data['show_whatsapp'] ?? true);
    $whatsappNumber = $data['whatsapp_number'] ?? '';
    $showPhone = (bool) ($data['show_phone'] ?? false);
    $phoneNumber = $data['phone_number'] ?? '';
    $showScrollTop = (bool) ($data['show_scroll_top'] ?? true);
    $positionClass = $position === 'bottom-start' ? 'start-4' : 'end-4';
@endphp

<div class="fixed bottom-4 {{ $positionClass }} z-40 flex flex-col items-center gap-3">
    @if($showWhatsapp && filled($whatsappNumber))
    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer" aria-label="واتساب" class="flex h-12 w-12 items-center justify-center rounded-full bg-green-500 text-white shadow-lg transition hover:bg-green-600">
        <iconify-icon icon="hugeicons:whatsapp" class="text-2xl" stroke-width="1.5"></iconify-icon>
    </a>
    @endif

    @if($showPhone && filled($phoneNumber))
    <a href="tel:{{ $phoneNumber }}" aria-label="اتصال" class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg transition hover:bg-primary-700">
        <iconify-icon icon="hugeicons:call-02" class="text-2xl" stroke-width="1.5"></iconify-icon>
    </a>
    @endif

    @if($showScrollTop)
    <button
        type="button"
        x-data="{ shown: false }"
        x-init="shown = window.scrollY > 300"
        x-on:scroll.window="shown = window.scrollY > 300"
        x-show="shown"
        x-transition
        x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        aria-label="العودة للأعلى"
        class="flex h-12 w-12 items-center justify-center rounded-full bg-stone-800 text-white shadow-lg transition hover:bg-stone-900"
    >
        <iconify-icon icon="hugeicons:arrow-up-01" class="text-2xl" stroke-width="1.5"></iconify-icon>
    </button>
    @endif
</div>
