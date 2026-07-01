<x-tenant::services.layout>
    @php
        $slug = request()->route('slug');
        $services = [
            'interior-finishing' => [
                'name' => 'تشطيب داخلي كامل',
                'price' => '4,500 ر.س',
                'description' => 'تشطيب كامل يشمل الأرضيات، الجدران، الأسقف، والإنارة مع متابعة تنفيذ دقيقة حتى التسليم.',
            ],
            'premium-parquet' => [
                'name' => 'تركيب باركيه فاخر',
                'price' => '2,100 ر.س',
                'description' => 'اختيار نوع الباركيه المناسب وتركيب احترافي مع معالجة الأطراف وتسليم نهائي نظيف.',
            ],
            'marble-alternative' => [
                'name' => 'توريد وتركيب بديل الرخام',
                'price' => '1,750 ر.س',
                'description' => 'حل اقتصادي وأنيق للجدران والمداخل بخامات عالية الجودة ومقاومة للرطوبة.',
            ],
            '3d-design' => [
                'name' => 'تصميم ثلاثي الأبعاد',
                'price' => '900 ر.س',
                'description' => 'نماذج 3D للمساحات مع توزيع الألوان والخامات قبل بدء التنفيذ.',
            ],
        ];

        $service = $services[$slug] ?? $services['interior-finishing'];
    @endphp

    <div class="mb-5 flex items-center justify-between px-2">
        <a href="{{ route('tenant.services.index') }}" wire:navigate class="flex h-10 w-10 rotate-180 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7M19 12H5"></path>
            </svg>
        </a>
    </div>

    <section class="mb-8 w-full px-3" x-data>
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2">
            <div>
                <div class="mb-4 aspect-square overflow-hidden rounded-2xl bg-stone-100">
                    <img id="serviceMainImage" src="https://images.unsplash.com/photo-1616594039964-3f3fbc5b5331?q=80&w=1200&auto=format&fit=crop" alt="{{ $service['name'] }}" class="h-full w-full object-cover">
                </div>
                <div class="flex gap-3 overflow-x-auto pb-2">
                    <button type="button" class="service-gallery-item h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border-2 border-stone-900 bg-stone-100">
                        <img src="https://images.unsplash.com/photo-1616594039964-3f3fbc5b5331?q=80&w=500&auto=format&fit=crop" alt="view 1" class="h-full w-full object-cover">
                    </button>
                    <button type="button" class="service-gallery-item h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border-2 border-transparent bg-stone-100 hover:border-stone-300">
                        <img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=500&auto=format&fit=crop" alt="view 2" class="h-full w-full object-cover">
                    </button>
                    <button type="button" class="service-gallery-item h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border-2 border-transparent bg-stone-100 hover:border-stone-300">
                        <img src="https://images.unsplash.com/photo-1615529162924-f8605388464d?q=80&w=500&auto=format&fit=crop" alt="view 3" class="h-full w-full object-cover">
                    </button>
                    <button type="button" class="service-gallery-item h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border-2 border-transparent bg-stone-100 hover:border-stone-300">
                        <img src="https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=500&auto=format&fit=crop" alt="view 4" class="h-full w-full object-cover">
                    </button>
                </div>
            </div>

            <div>
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h1 class="mb-2 text-2xl font-bold tracking-tight text-stone-900">{{ $service['name'] }}</h1>
                        <p class="text-sm text-stone-500">خدمة مخصصة لتشطيبات وديكور المساحات الداخلية.</p>
                    </div>
                    <span class="rounded-lg bg-primary-50 px-3 py-1.5 text-sm font-semibold text-primary-700">{{ $service['price'] }}</span>
                </div>

                <div class="mb-6 rounded-2xl border border-stone-200 bg-stone-50 p-4">
                    <h3 class="mb-2 text-sm font-semibold text-stone-900">وصف الخدمة</h3>
                    <p class="text-sm leading-7 text-stone-600">{{ $service['description'] }}</p>
                </div>

                <ul class="mb-6 space-y-2 text-sm text-stone-600">
                    <li class="rounded-xl bg-stone-50 px-3 py-2">- معاينة الموقع وتحديد الاحتياج</li>
                    <li class="rounded-xl bg-stone-50 px-3 py-2">- جدول زمني واضح للتنفيذ</li>
                    <li class="rounded-xl bg-stone-50 px-3 py-2">- فريق تنفيذ محترف وضمان جودة</li>
                </ul>

                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-primary-600 px-5 py-3 text-sm font-semibold text-white hover:bg-primary-700"
                    x-on:click="$dispatch('set-booking-service', { service: '{{ $service['name'] }}' }); $dispatch('open-modal', { name: 'service-booking-modal' })"
                >
                    طلب الخدمة
                </button>
            </div>
        </div>
    </section>

    <x-tenant::modal name="service-booking-modal" maxWidth="lg">
        <x-slot:title>طلب حجز خدمة</x-slot:title>

        <form class="space-y-4" x-data="{ serviceName: '' }" x-on:set-booking-service.window="serviceName = $event.detail.service">
            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">الخدمة</label>
                <input type="text" x-model="serviceName" readonly class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">الاسم</label>
                <input type="text" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none" placeholder="اكتب اسمك">
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">رقم الجوال</label>
                <input type="tel" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none" placeholder="05xxxxxxxx" dir="ltr">
            </div>
            <button type="button" class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white hover:bg-primary-700">
                إرسال الطلب
            </button>
        </form>
    </x-tenant::modal>

    <script>
        document.querySelectorAll('.service-gallery-item').forEach((button) => {
            button.addEventListener('click', () => {
                const mainImage = document.getElementById('serviceMainImage');
                const image = button.querySelector('img');

                if (mainImage && image) {
                    mainImage.src = image.src;
                }

                document.querySelectorAll('.service-gallery-item').forEach((item) => {
                    item.classList.remove('border-stone-900');
                    item.classList.add('border-transparent');
                });

                button.classList.remove('border-transparent');
                button.classList.add('border-stone-900');
            });
        });
    </script>
</x-tenant::services.layout>

<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
