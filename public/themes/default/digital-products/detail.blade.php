<x-tenant-theme::digital-products.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => route('tenant.digital-products.index'), 'title' => 'المنتجات الرقمية'], ['url' => null, 'title' => $product->title]]" />

    <div class="flex items-center justify-between mb-5 px-2">
        <a href="{{ route('tenant.digital-products.index') }}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="w-5 h-5 text-stone-700"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
        </a>
    </div>

    <section class="px-3 mb-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            @if ($images !== [])
                <div x-data="{ activeImage: @js($images[0]) }">
                    <div class="aspect-square bg-stone-100 rounded-2xl mb-4 overflow-hidden">
                        <img :src="activeImage" alt="{{ $product->title }}" class="w-full h-full object-cover">
                    </div>

                    @if (count($images) > 1)
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            @foreach ($images as $image)
                                <button type="button" @click="activeImage = @js($image)" class="shrink-0 w-20 h-20 bg-stone-100 rounded-lg overflow-hidden border-2" :class="activeImage === @js($image) ? 'border-stone-900' : 'border-transparent hover:border-stone-300'">
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
                @if ($categories->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <a href="{{ route('tenant.digital-products.index', ['category' => $category->slug]) }}" wire:navigate class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900 mb-2">{{ $product->title }}</h1>

                @if ($subtitle !== '')
                    <p class="text-stone-500 mb-4">{{ $subtitle }}</p>
                @endif

                <div class="flex flex-wrap items-center gap-3 mb-6">
                    @if ($price > 0)
                        <span class="text-3xl font-bold text-primary-600" dir="ltr">{{ money_format($price) }}</span>
                    @endif

                    @if (filled($comparePrice) && (int) $comparePrice > (int) $price)
                        <span class="text-lg text-stone-400 line-through" dir="ltr">{{ money_format($comparePrice) }}</span>
                    @endif

                    @if ($downloadsCount > 0)
                        <span class="rounded-lg bg-stone-100 px-3 py-1.5 text-sm text-stone-700">
                            {{ $downloadsCount }} {{ $downloadsCount === 1 ? 'ملف' : 'ملفات' }} للتحميل
                        </span>
                    @endif
                </div>

                @if ($body !== '')
                    <div class="prose prose-stone max-w-none text-stone-600 leading-8 mb-6">
                        {!! $body !!}
                    </div>
                @endif

                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-bold text-white hover:bg-primary-700 transition">
                    <iconify-icon icon="hugeicons:shopping-bag-01" class="text-xl"></iconify-icon>
                    شراء المنتج
                </button>
            </div>
        </div>
    </section>
</x-tenant-theme::digital-products.layout>
