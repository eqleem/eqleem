<x-tenant-theme::playlists.layout desc="اختر قائمة تشغيل ثم ادخل للتفاصيل والتشغيل الكامل.">
    <section class="p-1">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach($playlists as $playlist)
                <a
                    href="{{ route('tenant.playlists.detail', $playlist['slug']) }}"
                    wire:navigate
                    wire:key="playlist-{{ $playlist['slug'] }}"
                    class="group overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:border-primary-300 hover:shadow-sm"
                >
                    <div class="relative">
                        <img src="{{ $playlist['image'] }}" alt="{{ $playlist['name'] }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-105">
                        <span class="absolute end-3 top-3 rounded-full bg-black/65 px-2.5 py-1 text-[11px] font-semibold text-white">
                            {{ $playlist['type'] === 'video' ? 'فيديو' : 'صوتي' }}
                        </span>
                    </div>

                    <div class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-base font-bold text-stone-900 md:text-lg">{{ $playlist['name'] }}</h3>
                            <span class="rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700">
                                {{ $playlist['items_count'] }} مواد
                            </span>
                        </div>

                        <p class="line-clamp-2 text-sm text-stone-500">{{ $playlist['description'] }}</p>

                        <div class="inline-flex items-center gap-2 rounded-xl bg-stone-100 px-3 py-2 text-sm font-semibold text-stone-700 transition group-hover:bg-primary-50 group-hover:text-primary-700">
                            عرض القائمة
                            <iconify-icon icon="solar:arrow-left-linear" class="text-base"></iconify-icon>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</x-tenant-theme::playlists.layout>