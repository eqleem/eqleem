<x-tenant-theme::module-layout>
    <section class="mb-6">
        <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => $page->title]]" />
    </section>

    <section class="mt-8">
        <div class="mx-auto max-w-7xl px-2">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl lg:text-5xl">{{ $page->title }}</h2>
                @if ($subtitle !== '')
                    <p class="mt-4 text-base font-normal leading-7 text-gray-600 lg:mt-6 lg:text-lg lg:leading-8">{{ $subtitle }}</p>
                @endif
            </div>

            @if (count($faqs) > 0)
                <div class="mx-auto mt-12 max-w-5xl divide-y divide-gray-200 overflow-hidden rounded-xl border border-gray-200 sm:mt-16" x-data="{ active: 1 }">
                    @foreach ($faqs as $faq)
                        <div wire:key="faq-{{ $faq['id'] !== '' ? $faq['id'] : $loop->index }}" role="region">
                            <h3>
                                <button
                                    type="button"
                                    x-on:click="active = active === {{ $loop->iteration }} ? null : {{ $loop->iteration }}"
                                    x-bind:aria-expanded="active === {{ $loop->iteration }}"
                                    class="flex w-full items-center justify-between px-6 py-5 text-start text-lg font-semibold text-gray-900 sm:p-6"
                                >
                                    <span>{{ $faq['question'] }}</span>
                                    <span x-show="active === {{ $loop->iteration }}" aria-hidden="true" class="ms-4">
                                        <svg class="h-6 w-6 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </span>
                                    <span x-show="active !== {{ $loop->iteration }}" aria-hidden="true" class="ms-4">
                                        <svg class="h-6 w-6 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </span>
                                </button>
                            </h3>

                            <div x-cloak x-show="active === {{ $loop->iteration }}" x-collapse>
                                <div class="px-6 pb-6">
                                    <p class="text-base text-gray-600 whitespace-pre-line">{{ $faq['answer'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mx-auto mt-12 max-w-5xl rounded-xl border border-dashed border-gray-200 px-6 py-12 text-center text-base text-gray-500">
                    لا توجد أسئلة متكررة بعد.
                </div>
            @endif

            <div class="mx-auto mt-8 max-w-5xl overflow-hidden rounded-xl bg-gray-100 text-center sm:mt-12">
                <div class="px-6 py-12 sm:p-12">
                    <div class="mx-auto max-w-sm">
                        <h3 class="text-2xl font-semibold text-gray-900">ما زال لديك أسئلة؟</h3>
                        <p class="mt-2 text-base font-normal text-gray-600">لم تجد الإجابة التي تبحث عنها؟ تواصل معنا وسنساعدك.</p>
                        <div class="mt-6">
                            <a
                                href="{{ route('tenant.pages.contact') }}"
                                wire:navigate
                                class="inline-flex items-center justify-center rounded-full border border-transparent bg-primary-600 px-6 py-3 text-base font-medium text-white transition-all duration-200 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-700 focus:ring-offset-2"
                                role="button"
                            >
                                تواصل معنا
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-tenant-theme::module-layout>
