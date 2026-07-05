<x-tenant-theme::services.layout>
    <div class="mb-5 flex items-center justify-between px-2">
        <a href="{{ route('tenant.services.index') }}" wire:navigate class="flex h-10 w-10 rotate-180 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7M19 12H5"></path>
            </svg>
        </a>
    </div>

    @if ($addedToCart)
        <div class="mx-3 mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            تمت إضافة الحجز إلى السلة.
            <a href="{{ route('tenant.pages.cart') }}" wire:navigate class="ms-1 font-semibold underline">عرض السلة</a>
        </div>
    @endif

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
                            <span >{{ money_format($price) }}</span>
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
                    wire:click="openBookingModal"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white hover:bg-primary-700 md:w-auto"
                >
                    <iconify-icon icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                    حجز الخدمة
                </button>
            </div>
        </div>
    </section>

    <x-tenant-theme::modal name="service-booking-modal" maxWidth="lg">
        <x-slot:title>حجز الخدمة</x-slot:title>

        <form wire:submit="addToCart" class="space-y-4">
            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">الخدمة</label>
                <input type="text" value="{{ $service->title }}" readonly class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
            </div>

            @if ($calendars === [])
                <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    لا يوجد تقويم مرتبط بهذه الخدمة. يرجى التواصل مع المتجر لإتمام الحجز.
                </p>
            @else
                @if (count($calendars) > 1)
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-stone-700">التقويم</label>
                        <select
                            wire:model.live="calendarId"
                            class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                        >
                            <option value="">اختر التقويم ..</option>
                            @foreach ($calendars as $calendar)
                                <option value="{{ $calendar['id'] }}">{{ $calendar['name'] }}</option>
                            @endforeach
                        </select>
                        @error('calendarId')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if ($calendarId && $availableDates !== [])
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-stone-700">تاريخ الحجز</label>
                        <select
                            wire:model.live="bookingDate"
                            class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                            dir="ltr"
                        >
                            <option value="">اختر التاريخ ..</option>
                            @foreach ($availableDates as $availableDate)
                                <option value="{{ $availableDate }}">
                                    {{ \Carbon\Carbon::parse($availableDate)->translatedFormat('l j F Y') }}
                                </option>
                            @endforeach
                        </select>
                        @error('bookingDate')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @elseif ($calendarId)
                    <p class="text-sm text-amber-700">لا توجد تواريخ متاحة في التقويم المحدد.</p>
                @endif

                @if ($bookingDate !== '' && $timeSlots !== [])
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-stone-700">وقت الحجز</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($timeSlots as $slot)
                                @if ($slot['available'] ?? false)
                                    <button
                                        type="button"
                                        wire:click="selectTimeSlot(@js($slot['start_at']), @js($slot['end_at']))"
                                        class="rounded-xl border px-3 py-2 text-sm transition {{ $bookingStartAt === $slot['start_at'] && $bookingEndAt === $slot['end_at'] ? 'border-primary-500 bg-primary-50 font-semibold text-primary-700' : 'border-stone-200 bg-white text-stone-700 hover:bg-stone-50' }}"
                                        dir="ltr"
                                    >
                                        {{ $slot['label'] }}
                                    </button>
                                @else
                                    <span
                                        title="{{ ($slot['unavailable_reason'] ?? '') === 'booked' ? 'محجوز' : 'غير متاح' }}"
                                        class="cursor-not-allowed select-none rounded-xl border border-stone-100 bg-stone-50 px-3 py-2 text-sm text-stone-300 line-through"
                                        dir="ltr"
                                    >
                                        {{ $slot['label'] }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                        @error('bookingStartAt')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @elseif ($bookingDate !== '')
                    <p class="text-sm text-amber-700">لا توجد فترات في هذا التاريخ.</p>
                @endif

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    @disabled(! $calendarId || $bookingDate === '' || blank($bookingStartAt))
                    class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="addToCart">إضافة إلى السلة</span>
                    <span wire:loading wire:target="addToCart">جاري الإضافة...</span>
                </button>
            @endif
        </form>
    </x-tenant-theme::modal>
</x-tenant-theme::services.layout>
