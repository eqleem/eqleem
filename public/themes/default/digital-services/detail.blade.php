<x-tenant-theme::digital-services.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => route('tenant.digital-services.index'), 'title' => 'الخدمات الرقمية'], ['url' => null, 'title' => $service->title]]" />

    <div class="flex items-center justify-between mb-5 px-2">
        <a href="{{ route('tenant.digital-services.index') }}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="w-5 h-5 text-stone-700"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
        </a>
    </div>

    @if ($addedToCart)
        <div class="mx-3 mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            تمت إضافة الخدمة إلى السلة.
            <a href="{{ route('tenant.pages.cart') }}" wire:navigate class="ms-1 font-semibold underline">عرض السلة</a>
        </div>
    @endif

    <section class="px-3 mb-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            @if ($images !== [])
                <div x-data="{ activeImage: @js($images[0]) }">
                    <div class="aspect-square bg-stone-100 rounded-2xl mb-4 overflow-hidden">
                        <img :src="activeImage" alt="{{ $service->title }}" class="w-full h-full object-cover">
                    </div>

                    @if (count($images) > 1)
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            @foreach ($images as $image)
                                <button type="button" @click="activeImage = @js($image)" class="shrink-0 w-20 h-20 bg-stone-100 rounded-lg overflow-hidden border-2" :class="activeImage === @js($image) ? 'border-stone-900' : 'border-transparent hover:border-stone-300'">
                                    <img src="{{ $image }}" alt="{{ $service->title }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif ($imageUrl)
                <div class="aspect-square bg-stone-100 rounded-2xl overflow-hidden">
                    <img src="{{ $imageUrl }}" alt="{{ $service->title }}" class="w-full h-full object-cover">
                </div>
            @endif

            <div>
                @if ($categories->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <a href="{{ route('tenant.digital-services.index', ['category' => $category->slug]) }}" wire:navigate class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900 mb-2">{{ $service->title }}</h1>

                @if ($subtitle !== '')
                    <p class="text-stone-500 mb-4">{{ $subtitle }}</p>
                @endif

                <div class="flex flex-wrap items-center gap-3 mb-6">
                    @if ($price > 0)
                        <span class="text-3xl font-bold text-primary-600" >{{ money_format($price) }}</span>
                    @endif

                    @if ($deliveryDays > 0)
                        <span class="rounded-lg bg-stone-100 px-3 py-1.5 text-sm text-stone-700">
                            التسليم خلال {{ $deliveryDays }} {{ $deliveryDays === 1 ? 'يوم عمل' : 'أيام عمل' }}
                        </span>
                    @endif
                </div>

                @if ($body !== '')
                    <div class="prose prose-stone max-w-none text-stone-600 leading-8 mb-6">
                        {!! $body !!}
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    <div class="flex h-12 items-center rounded-xl border border-stone-200 px-2">
                        <button type="button" wire:click="decrementQuantity" class="flex h-8 w-8 items-center justify-center text-stone-500 hover:text-stone-900" aria-label="تقليل الكمية">−</button>
                        <span class="w-8 text-center text-sm font-medium">{{ $quantity }}</span>
                        <button type="button" wire:click="incrementQuantity" class="flex h-8 w-8 items-center justify-center text-stone-500 hover:text-stone-900" aria-label="زيادة الكمية">+</button>
                    </div>

                    <button
                        type="button"
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        wire:target="addToCart"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-bold text-white hover:bg-primary-700 transition disabled:opacity-70"
                    >
                        <iconify-icon icon="hugeicons:shopping-bag-01" class="text-xl"></iconify-icon>
                        <span wire:loading.remove wire:target="addToCart">أضف للسلة</span>
                        <span wire:loading wire:target="addToCart">جاري الإضافة...</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</x-tenant-theme::digital-services.layout>
