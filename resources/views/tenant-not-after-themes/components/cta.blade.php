<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

    <!-- Primary CTA -->
    <div class="w-full mb-6 animate-fade-in-up delay-300 flex gap-4">
        <button type="button" x-on:click="$dispatch('open-modal', { name: 'booking-modal' })" class="w-full flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 Xbg-gradient-to-r Xfrom-indigo-600 Xto-purple-600 Xhover:from-indigo-500 Xhover:to-purple-500 text-white text-base rounded-2xl px-4 py-3 font-medium transition-all duration-300 hover-lift" style="transform: translateY(0px);">
            <iconify-icon icon="hugeicons:calendar-add-01" class="inline text-3xl" stroke-width="1.5"></iconify-icon>
            حجز موعد تركيب
        </button>

        <a href="{{ route('tenant.pages.branches') }}" wire:navigate class="flex items-center text-white justify-center w-full bg-primary-600 hover:bg-primary-700 transition-all duration-200 xhover:-translate-y-px text-base font-medium  font-geist  rounded-2xl px-4 py-3 group relative overflow-hidden">
            <span class="relative z-10 flex items-center gap-2">
                {{-- <iconify-icon icon="solar:point-on-map-bold-duotone" class="inline text-3xl" stroke-width="1.5"></iconify-icon> --}}
                <iconify-icon icon="hugeicons:maps-square-02" class="inline text-3xl" stroke-width="1.5"></iconify-icon>
                زيارة الفرع
            </span>
        </a>
    </div>

    <x-tenant::modal name="booking-modal">
        <x-slot:title>
            حجز موعد تركيب
        </x-slot:title>

        <div class="space-y-4 text-right">
            <p class="text-sm leading-relaxed text-stone-600">
                اختر الطريقة المناسبة للتواصل معنا، وسننسّق معك موعد المعاينة والتركيب حسب الوقت المناسب لك.
            </p>

            <div class="grid gap-3">
                <a href="tel:+966500000000" class="flex items-center justify-between rounded-2xl border border-stone-200 px-4 py-3 text-stone-700 transition hover:border-primary-400 hover:bg-primary-50/40">
                    <span class="font-medium">اتصال هاتفي</span>
                    <iconify-icon icon="hugeicons:call-02" class="text-2xl text-primary-500" stroke-width="1.5"></iconify-icon>
                </a>

                <a href="https://wa.me/966500000000" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between rounded-2xl border border-stone-200 px-4 py-3 text-stone-700 transition hover:border-primary-400 hover:bg-primary-50/40">
                    <span class="font-medium">واتساب</span>
                    <iconify-icon icon="hugeicons:whatsapp" class="text-2xl text-primary-500" stroke-width="1.5"></iconify-icon>
                </a>
            </div>
        </div>

        <x-slot:footer>
            <button type="button" x-on:click="$dispatch('close-modal', { name: 'booking-modal' })" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-50">
                إغلاق
            </button>
        </x-slot:footer>
    </x-tenant::modal>