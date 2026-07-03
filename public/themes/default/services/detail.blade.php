<x-tenant-theme::services.layout>
    <div class="mb-5 flex items-center justify-between px-2">
        <a href="{{ route('tenant.services.index') }}" wire:navigate class="flex h-10 w-10 rotate-180 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7M19 12H5"></path>
            </svg>
        </a>
    </div>

    <section class="mb-8 w-full px-3" x-data>
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2">
            @if ($images !== [])
                <div x-data="{ activeImage: @js($images[0]) }">
                    <div class="mb-4 aspect-square overflow-hidden rounded-2xl bg-stone-100">
                        <img :src="activeImage" alt="{{ $service->title }}" class="h-full w-full object-cover">
                    </div>

                    @if (count($images) > 1)
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            @foreach ($images as $image)
                                <button
                                    type="button"
                                    @click="activeImage = @js($image)"
                                    class="h-20 w-20 shrink-0 overflow-hidden rounded-lg border-2 bg-stone-100"
                                    :class="activeImage === @js($image) ? 'border-stone-900' : 'border-transparent hover:border-stone-300'"
                                >
                                    <img src="{{ $image }}" alt="{{ $service->title }}" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif ($imageUrl)
                <div class="aspect-square overflow-hidden rounded-2xl bg-stone-100">
                    <img src="{{ $imageUrl }}" alt="{{ $service->title }}" class="h-full w-full object-cover">
                </div>
            @endif

            <div>
                @if ($categories->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <a href="{{ route('tenant.services.index', ['category' => $category->slug]) }}" wire:navigate class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h1 class="mb-2 text-2xl font-bold tracking-tight text-stone-900">{{ $service->title }}</h1>
                        @if ($subtitle !== '')
                            <p class="text-sm text-stone-500">{{ $subtitle }}</p>
                        @endif
                    </div>

                    <span class="rounded-lg bg-primary-50 px-3 py-1.5 text-sm font-semibold text-primary-700">
                        @if ($price > 0)
                            <span dir="ltr">{{ money_format($price) }}</span>
                        @else
                            حسب الطلب
                        @endif
                    </span>
                </div>

                @if ($durationMinutes > 0)
                    <p class="mb-4 text-sm text-stone-600">
                        مدة الخدمة: {{ $durationMinutes }} دقيقة
                    </p>
                @endif

                @if ($body !== '')
                    <div class="mb-6 rounded-2xl border border-stone-200 bg-stone-50 p-4 prose prose-stone max-w-none text-sm leading-7 text-stone-600">
                        {!! $body !!}
                    </div>
                @endif

                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white hover:bg-primary-700 md:w-auto"
                    x-on:click="$dispatch('set-booking-service', { service: @js($service->title) }); $dispatch('open-modal', { name: 'service-booking-modal' })"
                >
                    <iconify-icon icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                    حجز الخدمة
                </button>
            </div>
        </div>
    </section>

    <x-tenant-theme::modal name="service-booking-modal" maxWidth="lg">
        <x-slot:title>طلب حجز خدمة</x-slot:title>

        <form class="space-y-4" x-data="{ serviceName: @js($service->title) }" x-on:set-booking-service.window="serviceName = $event.detail.service">
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
                إرسال طلب الحجز
            </button>
        </form>
    </x-tenant-theme::modal>
</x-tenant-theme::services.layout>
