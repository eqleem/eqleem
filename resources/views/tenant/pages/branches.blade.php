<x-tenant::pages.layout>
 
    <section class="mb-6">
        <x-tenant::breadcrumb :links="[['url' => null, 'title' => 'فروعنا']]" />
        <x-tenant::page-title title="فروعنا" desc="اختر أقرب فرع لك وزره مباشرة" />
    </section>


    <section class="p-2 space-y-5">
        @foreach ($branches as $branch)
            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    <div class="h-64 lg:h-full">
                        <iframe
                            src="{{ $branch['map_embed_url'] }}"
                            class="h-full w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                            title="{{ $branch['name'] }}"
                        ></iframe>
                    </div>

                    <div class="space-y-4 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold text-stone-900">{{ $branch['name'] }}</h2>
                                <p class="mt-1 text-sm text-stone-600">{{ $branch['address'] }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-base"></iconify-icon>
                                مفتوح اليوم
                            </span>
                        </div>

                        <div class="rounded-xl bg-stone-50 p-4">
                            <p class="mb-2 text-xs font-semibold text-stone-500">أوقات العمل</p>
                            <ul class="space-y-1.5 text-sm text-stone-700">
                                @foreach ($branch['working_hours'] as $workingHour)
                                    <li wire:key="working-hour-{{ $branch['slug'] }}-{{ $loop->index }}" class="flex items-center justify-between gap-3">
                                        <span>{{ $workingHour['days'] }}</span>
                                        <span class="font-semibold">{{ $workingHour['time'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($branch['phones'] as $phone)
                                <a
                                    wire:key="branch-phone-{{ $branch['slug'] }}-{{ $loop->index }}"
                                    href="tel:{{ $phone['tel'] }}"
                                    class="inline-flex items-center justify-between rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-medium text-stone-700 hover:border-primary-300 hover:bg-primary-50/30"
                                >
                                    <span>{{ $phone['label'] }}</span>
                                    <span dir="ltr" class="font-semibold">{{ $phone['display'] }}</span>
                                </a>
                            @endforeach
                        </div>

                        <a
                            href="{{ $branch['google_map_url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
                        >
                            <iconify-icon icon="hugeicons:maps-square-02" class="text-xl"></iconify-icon>
                            التوجه إلى الموقع عبر Google Maps
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
</x-tenant::pages.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $branches = [];

    public function mount(): void
    {
        $this->branches = [
            [
                'slug' => 'riyadh-main',
                'name' => 'فرع الرياض - المروج',
                'address' => 'الرياض، حي المروج، طريق الأمير تركي بن عبدالعزيز الأول',
                'map_embed_url' => 'https://www.google.com/maps?q=24.774265,46.738586&z=14&output=embed',
                'google_map_url' => 'https://maps.google.com/?q=24.774265,46.738586',
                'working_hours' => [
                    ['days' => 'السبت - الخميس', 'time' => '9:00 ص - 11:00 م'],
                    ['days' => 'الجمعة', 'time' => '4:00 م - 11:30 م'],
                ],
                'phones' => [
                    ['label' => 'المبيعات', 'display' => '050 123 4567', 'tel' => '+966501234567'],
                    ['label' => 'خدمة العملاء', 'display' => '011 555 7788', 'tel' => '+966115557788'],
                ],
            ],
            [
                'slug' => 'jeddah-north',
                'name' => 'فرع جدة - أبحر الشمالية',
                'address' => 'جدة، أبحر الشمالية، طريق الأمير عبدالله الفيصل',
                'map_embed_url' => 'https://www.google.com/maps?q=21.543333,39.172779&z=14&output=embed',
                'google_map_url' => 'https://maps.google.com/?q=21.543333,39.172779',
                'working_hours' => [
                    ['days' => 'السبت - الخميس', 'time' => '10:00 ص - 10:30 م'],
                    ['days' => 'الجمعة', 'time' => '5:00 م - 11:00 م'],
                ],
                'phones' => [
                    ['label' => 'الاستقبال', 'display' => '053 765 4321', 'tel' => '+966537654321'],
                    ['label' => 'واتساب الفرع', 'display' => '054 987 1212', 'tel' => '+966549871212'],
                ],
            ],
            [
                'slug' => 'dammam-central',
                'name' => 'فرع الدمام - الشاطئ',
                'address' => 'الدمام، حي الشاطئ، طريق الخليج',
                'map_embed_url' => 'https://www.google.com/maps?q=26.420682,50.088795&z=14&output=embed',
                'google_map_url' => 'https://maps.google.com/?q=26.420682,50.088795',
                'working_hours' => [
                    ['days' => 'الأحد - الخميس', 'time' => '9:30 ص - 10:00 م'],
                    ['days' => 'السبت', 'time' => '11:00 ص - 9:30 م'],
                ],
                'phones' => [
                    ['label' => 'المعرض', 'display' => '055 112 3344', 'tel' => '+966551123344'],
                    ['label' => 'الدعم الفني', 'display' => '013 822 9090', 'tel' => '+966138229090'],
                ],
            ],
        ];
    }
};
?>
