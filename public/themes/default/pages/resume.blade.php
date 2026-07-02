<x-tenant-theme::pages.layout width="max-w-5xl">
    <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => 'السيرة الذاتية']]" />
    {{-- <x-tenant-theme::page-title title="السيرة الذاتية" desc="يمكنك تحميل السيرة الذاتية والتواصل معي مباشرة." /> --}}
    <section  class="space-y-6 mt-8 p-2">
        <div class="overflow-hidden rounded-3xl bg-white shadow-sm">
            <div class="bg-gradient-to-l from-primary-50 via-white to-stone-50 p-6 md:p-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-100 text-2xl font-black text-primary-700 md:h-20 md:w-20">
                            {{ mb_substr($fullName, 0, 1) }}
                        </div>
                        <div class="space-y-1">
                            <h1 class="text-2xl font-black text-stone-900 md:text-3xl">{{ $fullName }}</h1>
                            <p class="text-sm font-semibold text-primary-700 md:text-base">{{ $jobTitle }}</p>
                            <p class="text-sm text-stone-500">{{ $location }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 md:justify-end">
                        <a
                            href="javascript:void(0)"
                            onclick="window.print()"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                        >
                            <iconify-icon icon="hugeicons:file-download" class="text-lg"></iconify-icon>
                            تحميل السيرة
                        </a>

                        <a
                            href="mailto:{{ $email }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-primary-200 bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 transition hover:bg-primary-100"
                        >
                            <iconify-icon icon="hugeicons:mail-02" class="text-lg"></iconify-icon>
                            تواصل
                        </a>

                        <a
                            href="tel:{{ $phoneDial }}"
                            class="inline-flex items-center gap-2 rounded-xl  bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                        >
                            <iconify-icon icon="hugeicons:call" class="text-lg"></iconify-icon>
                            اتصال مباشر
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
            <div class="space-y-4 md:col-span-8">
                <article class="rounded-2xl  bg-white p-5 md:p-6">
                    <h2 class="mb-3 text-lg font-black text-stone-900">نبذة مختصرة</h2>
                    <p class="leading-8 text-stone-600">
                        {{ $summary }}
                    </p>
                </article>

                <article class="rounded-2xl  bg-white p-5 md:p-6">
                    <h2 class="mb-4 text-lg font-black text-stone-900">الخبرات العملية</h2>
                    <div class="space-y-4">
                        @foreach ($experiences as $experience)
                            <div class="rounded-xl bg-stone-50 p-4">
                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="text-base font-bold text-stone-900">{{ $experience['title'] }}</h3>
                                    <span class="rounded-full bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-700">{{ $experience['period'] }}</span>
                                </div>
                                <p class="text-sm font-medium text-stone-500">{{ $experience['company'] }}</p>
                                <p class="mt-2 text-sm leading-7 text-stone-600">{{ $experience['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-2xl  bg-white p-5 md:p-6">
                    <h2 class="mb-4 text-lg font-black text-stone-900">مشاريع مختارة</h2>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($projects as $project)
                            <div class="rounded-xl  p-4">
                                <h3 class="text-sm font-bold text-stone-900">{{ $project['name'] }}</h3>
                                <p class="mt-1 text-sm leading-7 text-stone-600">{{ $project['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>

            <aside class="space-y-4 md:col-span-4">
                <article class="rounded-2xl  bg-white p-5">
                    <h2 class="mb-4 text-base font-black text-stone-900">المهارات</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="rounded-full bg-stone-100 px-3 py-1.5 text-xs font-semibold text-stone-700">{{ $skill }}</span>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-2xl  bg-white p-5">
                    <h2 class="mb-4 text-base font-black text-stone-900">معلومات التواصل</h2>
                    <ul class="space-y-3 text-sm text-stone-600">
                        <li class="flex items-center gap-2">
                            <iconify-icon icon="hugeicons:mail-02" class="text-lg text-primary-600"></iconify-icon>
                            <a href="mailto:{{ $email }}" class="transition hover:text-primary-700">{{ $email }}</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <iconify-icon icon="hugeicons:call" class="text-lg text-primary-600"></iconify-icon>
                            <a href="tel:{{ $phoneDial }}" class="transition hover:text-primary-700">{{ $phone }}</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <iconify-icon icon="hugeicons:location-01" class="text-lg text-primary-600"></iconify-icon>
                            <span>{{ $location }}</span>
                        </li>
                    </ul>
                </article>

                <article class="rounded-2xl  bg-white p-5">
                    <h2 class="mb-4 text-base font-black text-stone-900">التعليم</h2>
                    <p class="text-sm font-semibold text-stone-700">{{ $education }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ $educationPeriod }}</p>
                </article>
            </aside>
        </div>
    </section>
</x-tenant-theme::pages.layout>