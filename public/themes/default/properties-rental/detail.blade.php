<x-tenant-theme::properties-rental.layout>
    <x-tenant-theme::breadcrumb :links="[
        ['url' => route('tenant.properties-rental.index'), 'title' => 'تأجير الوحدات'],
        ['url' => null, 'title' => $unit->title],
    ]" />

    <div class="mb-5 mt-3 flex items-center justify-between px-2">
        <a href="{{ route('tenant.properties-rental.index') }}" wire:navigate class="flex h-10 w-10 rotate-180 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
            <iconify-icon icon="solar:arrow-left-linear" class="text-xl text-stone-700"></iconify-icon>
        </a>
    </div>

    @if ($addedToCart)
        <div class="mx-3 mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            تمت إضافة الحجز إلى السلة.
            <a href="{{ route('tenant.pages.cart') }}" wire:navigate class="ms-1 font-semibold underline">عرض السلة</a>
        </div>
    @endif

    <section class="mb-8 w-full px-3">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2">
            @if ($images !== [])
                <div x-data="{ activeImage: @js($images[0]) }">
                    <div class="mb-4 aspect-square overflow-hidden rounded-2xl bg-stone-100">
                        <img :src="activeImage" alt="{{ $unit->title }}" class="h-full w-full object-cover">
                    </div>

                    @if (count($images) > 1)
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            @foreach ($images as $image)
                                <button
                                    type="button"
                                    @click="activeImage = @js($image)"
                                    class="gallery-nav h-20 w-20 shrink-0 overflow-hidden rounded-lg border-2 border-transparent bg-stone-100 hover:border-stone-300"
                                    :class="{ 'border-stone-900': activeImage === @js($image) }"
                                >
                                    <img src="{{ $image }}" alt="{{ $unit->title }}" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif ($imageUrl)
                <div class="aspect-square overflow-hidden rounded-2xl bg-stone-100">
                    <img src="{{ $imageUrl }}" alt="{{ $unit->title }}" class="h-full w-full object-cover">
                </div>
            @endif

            <div>
                @if ($categories->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <a href="{{ route('tenant.properties-rental.index', ['category' => $category->slug]) }}" wire:navigate class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-between gap-3">
                    <div>
                        <h1 class="mb-1 text-xl font-bold tracking-tight text-stone-900 md:text-2xl">{{ $unit->title }}</h1>
                        @if ($subtitle !== '')
                            <p class="text-sm text-stone-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>

                @if ($body !== '')
                    <div class="prose prose-stone mt-5 max-w-none text-sm leading-7 text-stone-600">
                        {!! $body !!}
                    </div>
                @endif

                @if ($pricePerNight > 0)
                    @php
                        $nights = max(\Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)), 1);
                        $rentalTotal = $pricePerNight * $nights;
                    @endphp

                    <div class="mt-6 rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <div class="border-b border-stone-100 px-5 py-4">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-2xl font-black text-primary-700">
                                        <span >{{ money_format($pricePerNight) }}</span>
                                        <span class="text-base font-bold"> / ليلة</span>
                                    </p>
                                    <p class="mt-1 text-xs text-stone-500">
                                        إجمالي {{ $nights }} {{ $nights === 1 ? 'ليلة' : 'ليالي' }}:
                                        <span >{{ money_format($rentalTotal) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 px-5 py-5">
                            @if ($calendars === [])
                                <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    لا يوجد تقويم مرتبط بهذه الوحدة.
                                </p>
                            @else
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="space-y-1 text-sm">
                                        <span class="font-medium text-stone-700">تاريخ الوصول</span>
                                        <input type="date" wire:model.live="checkIn" class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2 text-stone-700" dir="ltr">
                                        @error('checkIn')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </label>
                                    <label class="space-y-1 text-sm">
                                        <span class="font-medium text-stone-700">تاريخ المغادرة</span>
                                        <input type="date" wire:model.live="checkOut" min="{{ $checkIn }}" class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2 text-stone-700" dir="ltr">
                                        @error('checkOut')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </label>
                                </div>

                                <button
                                    type="button"
                                    wire:click="addToCart"
                                    wire:loading.attr="disabled"
                                    wire:target="addToCart"
                                    class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary-500 text-sm font-bold text-white transition hover:bg-primary-600 disabled:opacity-70"
                                >
                                    <iconify-icon icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                                    <span wire:loading.remove wire:target="addToCart">أضف للسلة</span>
                                    <span wire:loading wire:target="addToCart">جاري الإضافة...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-tenant-theme::properties-rental.layout>
