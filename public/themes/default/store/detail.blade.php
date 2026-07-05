<x-tenant-theme::store.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => route('tenant.store.index'), 'title' => 'المتجر'], ['url' => null, 'title' => $product->title]]" />

    <div class="flex items-center justify-between mb-5 px-2">
        <a href="{{ route('tenant.store.index') }}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="w-5 h-5 text-stone-700"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition" aria-label="مشاركة المنتج">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="w-5 h-5 text-stone-700"><path d="M12 2v13"></path><path d="m16 6-4-4-4 4"></path><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path></svg>
            </button>
            <button type="button" class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition" aria-label="إضافة إلى المفضلة">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="h-4 w-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            </button>
        </div>
    </div>

    @if ($addedToCart)
        <div class="mx-3 mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            تمت إضافة المنتج إلى السلة.
            <a href="{{ route('tenant.pages.cart') }}" wire:navigate class="font-semibold underline ms-1">عرض السلة</a>
        </div>
    @endif

    <section class="px-3 mb-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            @if ($images !== [])
                <div
                    x-data="{ activeImage: @js($images[0]) }"
                >
                    <div class="aspect-square bg-stone-100 rounded-2xl mb-4 overflow-hidden">
                        <img
                            :src="activeImage"
                            alt="{{ $product->title }}"
                            class="w-full h-full object-cover product-zoom"
                        >
                    </div>

                    @if (count($images) > 1)
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            @foreach ($images as $image)
                                <button
                                    type="button"
                                    @click="activeImage = @js($image)"
                                    class="gallery-nav shrink-0 w-20 h-20 bg-stone-100 rounded-lg overflow-hidden border-2"
                                    :class="activeImage === @js($image) ? 'border-stone-900' : 'border-transparent hover:border-stone-300'"
                                >
                                    <img src="{{ $image }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif ($imageUrl)
                <div class="aspect-square bg-stone-100 rounded-2xl overflow-hidden">
                    <img src="{{ $imageUrl }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                </div>
            @endif

            <div>
                <div class="flex justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="text-xl font-bold tracking-tight text-stone-900 mb-1 font-geist">{{ $product->title }}</h1>

                        @if ($categories->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                @foreach ($categories as $category)
                                    <a
                                        href="{{ route('tenant.store.index', ['category' => $category->slug]) }}"
                                        wire:navigate
                                        class="inline-flex items-center rounded-md bg-stone-100 px-2 py-1 text-xs font-medium text-stone-700 transition hover:bg-stone-200"
                                    >
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($price > 0)
                        <div class="text-end shrink-0">
                            <span class="text-2xl text-black font-semibold">{{ money_format($price) }}</span>
                            @if (filled($comparePrice) && (int) $comparePrice > (int) $price)
                                <p class="line-through text-sm text-stone-500">{{ money_format($comparePrice) }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-4 mb-8 mt-5">
                    <div class="flex items-center border border-stone-200 rounded-[12px] h-12 px-2">
                        <button type="button" wire:click="decrementQuantity" class="w-8 h-full flex items-center justify-center text-stone-500 hover:text-stone-900" aria-label="تقليل الكمية">
                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path></svg>
                        </button>
                        <span class="w-8 text-center text-sm font-medium">{{ $quantity }}</span>
                        <button type="button" wire:click="incrementQuantity" class="w-8 h-full flex items-center justify-center text-stone-500 hover:text-stone-900" aria-label="زيادة الكمية">
                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7v14"></path></svg>
                        </button>
                    </div>

                    <button
                        type="button"
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        wire:target="addToCart"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-primary-600 text-white px-8 py-2.5 rounded-xl font-semibold text-lg hover:bg-primary-700 transition-all duration-300 font-geist disabled:opacity-70"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                        <span wire:loading.remove wire:target="addToCart">أضف للسلة</span>
                        <span wire:loading wire:target="addToCart">جاري الإضافة...</span>
                    </button>
                </div>

                <div class="border-t border-stone-200 pt-8">
                    <h3 class="text-lg font-semibold mb-4 font-geist">تفاصيل المنتج</h3>

                    @if ($body !== '')
                        <div class="prose prose-stone max-w-none space-y-3 text-sm text-stone-600 mb-6">
                            {!! $body !!}
                        </div>
                    @endif

                    @if (filled($weight))
                        <div class="grid grid-cols-2 gap-4 text-sm text-stone-600">
                            <div>
                                <span class="font-medium text-stone-900 font-geist">الوزن:</span>
                                <p class="font-geist" dir="ltr">{{ $weight }} كجم</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-tenant-theme::store.layout>
