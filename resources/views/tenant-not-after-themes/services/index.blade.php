<x-tenant::services.layout>
    <section class="p-1" x-data>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                <a href="{{ route('tenant.services.detail', 'interior-finishing') }}" wire:navigate class="block">
                    <img
                        src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=1200&auto=format&fit=crop"
                        alt="تشطيب داخلي كامل"
                        class="h-56 w-full object-cover"
                    >
                </a>
                <div class="space-y-3 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('tenant.services.detail', 'interior-finishing') }}" wire:navigate class="text-lg font-semibold text-stone-900 transition hover:text-primary-700">
                                تشطيب داخلي كامل
                            </a>
                            <p class="text-sm text-stone-500">من الفكرة حتى التسليم النهائي.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-3 py-1 text-sm font-semibold text-primary-700">
                            <iconify-icon icon="hugeicons:tag-01" class="text-base"></iconify-icon>
                            حسب الطلب
                        </span>
                    </div>
                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                        x-on:click="$dispatch('set-booking-service', { service: 'تشطيب داخلي كامل' }); $dispatch('open-modal', { name: 'service-booking-modal' })"
                    >
                        <iconify-icon icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                        حجز خدمة
                    </button>
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                <a href="{{ route('tenant.services.detail', 'premium-parquet') }}" wire:navigate class="block">
                    <img
                        src="https://images.unsplash.com/photo-1616594039964-3f3fbc5b5331?q=80&w=1200&auto=format&fit=crop"
                        alt="تركيب باركيه"
                        class="h-56 w-full object-cover"
                    >
                </a>
                <div class="space-y-3 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('tenant.services.detail', 'premium-parquet') }}" wire:navigate class="text-lg font-semibold text-stone-900 transition hover:text-primary-700">
                                تركيب باركيه فاخر
                            </a>
                            <p class="text-sm text-stone-500">اختيار خامة وتركيب احترافي مع الضمان.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-3 py-1 text-sm font-semibold text-primary-700">
                            <iconify-icon icon="hugeicons:tag-01" class="text-base"></iconify-icon>
                            حسب الطلب
                        </span>
                    </div>
                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                        x-on:click="$dispatch('set-booking-service', { service: 'تركيب باركيه فاخر' }); $dispatch('open-modal', { name: 'service-booking-modal' })"
                    >
                        <iconify-icon icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                        حجز خدمة
                    </button>
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                <a href="{{ route('tenant.services.detail', 'marble-alternative') }}" wire:navigate class="block">
                    <img
                        src="https://images.unsplash.com/photo-1615529162924-f8605388464d?q=80&w=1200&auto=format&fit=crop"
                        alt="بديل الرخام"
                        class="h-56 w-full object-cover"
                    >
                </a>
                <div class="space-y-3 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('tenant.services.detail', 'marble-alternative') }}" wire:navigate class="text-lg font-semibold text-stone-900 transition hover:text-primary-700">
                                توريد وتركيب بديل الرخام
                            </a>
                            <p class="text-sm text-stone-500">حل أنيق للمجالس والمداخل والجدران.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-3 py-1 text-sm font-semibold text-primary-700">
                            <iconify-icon icon="hugeicons:tag-01" class="text-base"></iconify-icon>
                            حسب الطلب
                        </span>
                    </div>
                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                        x-on:click="$dispatch('set-booking-service', { service: 'توريد وتركيب بديل الرخام' }); $dispatch('open-modal', { name: 'service-booking-modal' })"
                    >
                        <iconify-icon icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                        حجز خدمة
                    </button>
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                <a href="{{ route('tenant.services.detail', '3d-design') }}" wire:navigate class="block">
                    <img
                        src="https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop"
                        alt="تصميم ثلاثي الأبعاد"
                        class="h-56 w-full object-cover"
                    >
                </a>
                <div class="space-y-3 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('tenant.services.detail', '3d-design') }}" wire:navigate class="text-lg font-semibold text-stone-900 transition hover:text-primary-700">
                                تصميم ثلاثي الأبعاد
                            </a>
                            <p class="text-sm text-stone-500">تصور كامل للمشروع قبل بدء التنفيذ.</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-3 py-1 text-sm font-semibold text-primary-700">
                            <iconify-icon icon="hugeicons:tag-01" class="text-base"></iconify-icon>
                            حسب الطلب
                        </span>
                    </div>
                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                        x-on:click="$dispatch('set-booking-service', { service: 'تصميم ثلاثي الأبعاد' }); $dispatch('open-modal', { name: 'service-booking-modal' })"
                    >
                        <iconify-icon icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                        حجز خدمة
                    </button>
                </div>
            </article>
        </div>
    </section>

    <x-tenant::modal name="service-booking-modal" maxWidth="lg">
        <x-slot:title>طلب حجز خدمة</x-slot:title>

        <form class="space-y-4" x-data="{ serviceName: '' }" x-on:set-booking-service.window="serviceName = $event.detail.service">
            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">الخدمة</label>
                <input
                    type="text"
                    x-model="serviceName"
                    readonly
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700"
                >
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">الاسم</label>
                <input
                    type="text"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                    placeholder="اكتب اسمك"
                >
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">رقم الجوال</label>
                <input
                    type="tel"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                    placeholder="05xxxxxxxx"
                    dir="ltr"
                >
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">تفاصيل إضافية</label>
                <textarea
                    rows="4"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                    placeholder="أدخل مساحة المشروع أو ملاحظاتك"
                ></textarea>
            </div>

            <button
                type="button"
                class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white hover:bg-primary-700"
            >
                إرسال طلب الحجز
            </button>
        </form>
    </x-tenant::modal>
</x-tenant::services.layout>

<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
