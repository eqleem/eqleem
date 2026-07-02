<x-tenant-theme::layout >
    <section class="bg-stone-50 px-3 py-4 md:rounded-3xl md:px-8 md:py-10">
        <div class="">
            <main class="space-y-10  ">
                <header class="space-y-3">
                    <h1 class=" text-2xl font-black text-stone-900 md:text-4xl"> تحديثات المنصة </h1>
                    <p class=" text-sm text-stone-500 md:text-base">التغييرات، الإصلاحات، والتحسينات عبر الإصدارات المختلفة.</p>
                </header>

                <div class="space-y-12">
                    @foreach ($updates as $update)
                        <article id="{{ $update['id'] }}" class="scroll-mt-24 border-b-2 border-b-stone-200/50 pb-10 last:border-b-0">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-[170px_minmax(0,1fr)] md:gap-12">
                                <div class="md:pt-2">
                                    <div class="inline-flex rounded-lg bg-primary-100/70 px-3 py-1.5 text-sm font-bold text-stone-800">
                                        {{ $update['date'] }}
                                    </div>

                                    <ul class="mt-4 text-sm leading-6 text-stone-600 flex flex-wrap gap-1">
                                        @foreach ($update['highlights'] as $highlight)
                                            <li class="text-xs bg-stone-200/50 px-1.5 py-1 rounded-md">{{ $highlight }}</li>
                                        @endforeach
                                    </ul>

                                    {{-- <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($update['labels'] as $label)
                                            <span class="rounded-md bg-stone-200/70 px-2 py-1 text-xs font-medium text-stone-700">{{ $label }}</span>
                                        @endforeach
                                    </div> --}}
                                </div>

                                <div class="space-y-10">
                                    @foreach ($update['changes'] as $change)
                                        <section class="space-y-1">
                                            <h2 class="text-lg font-black leading-tight text-stone-900">{{ $change['title'] }}</h2>
                                            <p class="text-base leading-8 text-stone-600">
                                                {{ $change['description'] }}
                                            </p>
                                        </section>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </main>

            {{-- <aside class="lg:col-span-3">
                <div class="space-y-6 lg:sticky lg:top-24">
                 
                    <div class="rounded-xl border border-stone-200 bg-white p-4">
                        <h3 class="mb-4  text-sm font-bold text-stone-900">في هذه الصفحة</h3>
                        <ul class="space-y-1 border-r border-stone-200 pr-3">
                            @foreach ($updates as $index => $update)
                                <li>
                                    <a
                                        href="#{{ $update['id'] }}"
                                        class="block rounded-md px-2 py-1.5 text-sm transition {{ $index === 0 ? 'font-bold text-stone-900' : 'text-stone-500 hover:bg-stone-100 hover:text-stone-800' }}"
                                    >
                                        {{ $update['date'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </aside> --}}
        </div>
    </section>
</x-tenant-theme::layout>