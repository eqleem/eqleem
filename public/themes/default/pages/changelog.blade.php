<x-tenant-theme::pages.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => 'تحديثات المنصة']]" />
    <x-tenant-theme::page-title title="تحديثات المنصة" desc="التغييرات، الإصلاحات، والتحسينات عبر الإصدارات المختلفة." />
    <section class="p-3">
        <main class=" ">
            
            <div class="space-y-24 mt-6">
                @foreach ($releases as $release)
                    <article id="{{ $release['id'] }}" class="scroll-mt-24">
                        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-stone-500 border-b-2 border-dotted border-stone-300 pb-4">
                            <span class="rounded-md bg-stone-200/30 px-2 py-1 font-semibold text-stone-700">إصدار</span>
                            <span>•</span>
                            <span>{{ $release['date'] }}</span>
                        </div>

                        <div class="space-y-2 bg-stone-200/30 px-4 py-2 rounded-md">
                        <h2 class="text-2xl font-black text-stone-900 md:text-3xl">{{ $release['title'] }}</h2>
                        <p class="mt-2 max-w-4xl text-sm leading-8 text-stone-500">{{ $release['summary'] }}</p>
                        </div>

                        <div class="mt-8 space-y-8">
                            @foreach ($release['sections'] as $section)
                                <section class="space-y-4">
                                    <div class="inline-flex rounded-md bg-primary-100 px-2.5 py-1 text-xs font-semibold text-stone-700">
                                        {{ $section['label'] }}
                                    </div>

                                    <div class="space-y-4">
                                        @foreach ($section['items'] as $item)
                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-[220px_minmax(0,1fr)] md:gap-5">
                                                <h3 class="text-sm font-semibold text-stone-800 Xbg-stone-100 px-2 py-1 rounded-md inline-block">{{ $item['title'] }}</h3>
                                                <p class="text-sm leading-7 text-stone-500 xbg-stone-100 px-2 py-1 rounded-md w-full">{{ $item['description'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </main>
    </section>
</x-tenant-theme::pages.layout>