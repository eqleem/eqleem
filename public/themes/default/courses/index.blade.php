<x-tenant-theme::courses.layout>
    <section class="px-1 mb-5 w-full flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 rounded-2xl p-1 whitespace-nowrap w-full">
            <button class="p-3 text-center py-2.5 rounded-xl bg-white text-stone-900 text-sm font-medium shadow-sm">الكل</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">أعمال الباركيه</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">بديل الرخام</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">بديل الخشب</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">إدارة المشاريع</button>
        </div>

        <div class="flex items-center gap-3">
            <button class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
            </button>
        </div>
    </section>

    <section class="p-1">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
            @foreach($courses as $course)
                <a
                    wire:key="course-{{ $course['slug'] }}"
                    href="{{ route('tenant.courses.detail', $course['slug']) }}"
                    wire:navigate
                    class="group rounded-xl md:rounded-2xl overflow-hidden bg-white transition animate-on-scroll animate"
                >
                    <div class="relative">
                        <img src="{{ $course['image'] }}" alt="{{ $course['title'] }}" class="w-full h-56 md:h-64 object-cover group-hover:scale-105 transition-all duration-500">
                        <div class="absolute top-2 end-2 inline-flex items-center rounded-full bg-black/50 px-3 py-1 text-xs text-white backdrop-blur">
                            {{ $course['level'] }}
                        </div>
                    </div>

                    <div class="p-4 border border-neutral-200 border-t-0 rounded-b-xl md:rounded-b-2xl">
                        <p class="text-xs text-primary-600 font-semibold mb-1">{{ $course['category'] }}</p>
                        <h3 class="text-lg font-semibold tracking-tight font-geist text-stone-900">{{ $course['title'] }}</h3>

                        <div class="mt-4 flex items-center justify-between text-sm text-stone-600">
                            <span class="inline-flex items-center gap-1">
                                <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-lg"></iconify-icon>
                                {{ $course['hours'] }} ساعة
                            </span>
                            <span class="text-xl font-bold text-stone-900">{{ $course['price'] }} ر.س</span>
                        </div>

                        <div class="mt-4 inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-primary-50 text-primary-600 text-sm font-semibold group-hover:bg-primary-100 transition">
                            عرض التفاصيل
                            <iconify-icon icon="solar:arrow-left-linear" class="text-base"></iconify-icon>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</x-tenant-theme::courses.layout>
