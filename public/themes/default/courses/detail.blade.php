<x-tenant-theme::courses.layout>
    <div class="flex items-center justify-between mb-5 px-2">
        <a href="{{ route('tenant.courses.index') }}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="w-5 h-5 text-stone-700"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
        </a>
    </div>

    <section class="px-3 mb-8 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <div class="space-y-4">
                <div class="aspect-video bg-stone-100 rounded-2xl overflow-hidden">
                    <img src="{{ $imageUrl }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs text-stone-500 mb-1">المستوى</p>
                        <p class="text-sm font-semibold text-stone-900">{{ $levelLabel }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs text-stone-500 mb-1">عدد الساعات</p>
                        <p class="text-sm font-semibold text-stone-900">
                            @if ($hours > 0)
                                {{ $hours }} ساعة تدريبية
                            @else
                                {{ $lessonCount }} دروس
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div>
                @if ($categories->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <a
                                href="{{ route('tenant.courses.index', ['category' => $category->slug]) }}"
                                wire:navigate
                                class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700"
                            >
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3 mb-4">
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900">{{ $course->title }}</h1>
                    @if ($price > 0)
                        <span class="text-2xl font-bold text-primary-600" dir="ltr">{{ money_format($price) }}</span>
                    @endif
                </div>

                @if ($subtitle !== '')
                    <p class="text-stone-600 leading-8 mb-4">{{ $subtitle }}</p>
                @endif

                @if ($body !== '')
                    <div class="prose prose-stone max-w-none text-stone-600 leading-8">
                        {!! $body !!}
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-bold text-white hover:bg-primary-700 transition">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="text-xl"></iconify-icon>
                        الالتحاق بالدورة
                    </button>
                    <a href="{{ route('tenant.courses.index') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-stone-300 bg-white px-5 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                        <iconify-icon icon="solar:arrow-right-linear" class="text-xl"></iconify-icon>
                        الرجوع للدورات
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="px-3 pb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-stone-900">محتوى الدورة</h2>
            <span class="text-sm text-stone-500">{{ count($chapters) }} {{ count($chapters) === 1 ? 'فصل' : 'فصول' }} · {{ $lessonCount }} {{ $lessonCount === 1 ? 'درس' : 'دروس' }}</span>
        </div>

        @if ($chapters === [])
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-sm text-stone-500">لم تُضف فصول ودروس لهذه الدورة بعد.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($chapters as $chapter)
                    <div wire:key="chapter-{{ $course->id }}-{{ $chapter['id'] }}" class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
                        <h3 class="text-base md:text-lg font-bold text-stone-900 mb-1">{{ $chapter['title'] }}</h3>

                        @if (filled($chapter['description']))
                            <p class="text-sm text-stone-500 mb-4">{{ $chapter['description'] }}</p>
                        @endif

                        <div class="space-y-3">
                            @foreach ($chapter['lessons'] as $lesson)
                                <div wire:key="lesson-{{ $course->id }}-{{ $lesson['id'] }}" class="flex flex-col md:flex-row md:items-center gap-3 rounded-xl border border-stone-100 bg-stone-50 p-3">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-stone-900">{{ $lesson['title'] }}</h4>

                                        @if (filled($lesson['description']))
                                            <p class="mt-1 text-sm text-stone-500">{{ $lesson['description'] }}</p>
                                        @endif

                                        @if (filled($lesson['file_name']))
                                            <p class="mt-1 text-xs text-stone-400">{{ $lesson['file_name'] }}</p>
                                        @endif
                                    </div>

                                    @if ($lesson['playable'])
                                        @if ($lesson['source'] === 'link' && filled($lesson['link']))
                                            <a
                                                href="{{ $lesson['link'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold bg-primary-600 text-white hover:bg-primary-700 transition"
                                            >
                                                <iconify-icon icon="solar:play-circle-bold-duotone" class="text-lg"></iconify-icon>
                                                تشغيل
                                            </a>
                                        @elseif (filled($lesson['file_url']))
                                            <a
                                                href="{{ $lesson['file_url'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold bg-primary-600 text-white hover:bg-primary-700 transition"
                                            >
                                                <iconify-icon icon="solar:play-circle-bold-duotone" class="text-lg"></iconify-icon>
                                                تشغيل
                                            </a>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold bg-stone-200 text-stone-500">
                                            <iconify-icon icon="solar:lock-keyhole-bold-duotone" class="text-lg"></iconify-icon>
                                            قريباً
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-tenant-theme::courses.layout>
