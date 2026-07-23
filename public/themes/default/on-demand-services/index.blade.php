<x-tenant-theme::on-demand-services.layout>
    <section class="px-1 w-full flex items-center justify-end gap-3">
        <div class="flex items-center gap-3" x-data="{ open: false }">
            <div x-show="open" x-transition class="hidden sm:block">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="ابحث في الخدمات..."
                    class="w-44 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 outline-none focus:border-stone-400 md:w-56"
                >
            </div>

            <button type="button" @click="open = !open" class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105" aria-label="البحث">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
            </button>
        </div>
    </section>

    <section class="p-1 mt-5">
        @if ($services->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد خدمات حسب الطلب حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر الخدمات المنشورة هنا عند إضافتها من لوحة التحكم.</p>
            </div>
        @else
            <div class="grid grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
                @foreach ($services as $item)
                    @php($service = $item['model'])

                    <article wire:key="on-demand-service-{{ $service->id }}" class="overflow-hidden rounded-xl bg-white transition md:rounded-2xl">
                        <a href="{{ route('tenant.on-demand-services.detail', $service->slug) }}" wire:navigate class="group block">
                            <div class="relative">
                                <img src="{{ $item['imageUrl'] }}" alt="{{ $service->title }}" class="h-56 w-full object-cover transition-all duration-500 group-hover:scale-105 md:h-72">
                            </div>
                        </a>

                        <div class="rounded-b-xl border border-neutral-200 border-t-0 p-3 md:rounded-b-2xl">
                            <a href="{{ route('tenant.on-demand-services.detail', $service->slug) }}" wire:navigate>
                                <h3 class="truncate text-lg font-semibold tracking-tight text-stone-900">{{ $service->title }}</h3>
                            </a>

                            @if ($item['subtitle'] !== '')
                                <p class="mt-0.5 line-clamp-2 text-xs text-neutral-600">{{ $item['subtitle'] }}</p>
                            @endif

                            <div class="mt-4">
                                @if ($item['priceHtml'])
                                    <p class="text-xl font-bold text-stone-900">{!! $item['priceHtml'] !!}</p>
                                @elseif ($item['unitDisplay'] !== '')
                                    <p class="text-sm text-stone-500">الوحدة: {{ $item['unitDisplay'] }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-tenant-theme::on-demand-services.layout>
