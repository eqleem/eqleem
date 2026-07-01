<x-tenant::pages.layout>
    <section class="mb-6">
        <x-tenant::breadcrumb :links="[['url' => null, 'title' => 'الأسئلة المتكررة']]" />
        {{-- <x-tenant::page-title title="الأسئلة المتكررة" desc="إجابات سريعة لأكثر الأسئلة شيوعا حول الخدمات، المواعيد، والتسعير." /> --}}
    </section>

    <section class="mt-8 ">
        <div class="mx-auto max-w-7xl px-2">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl lg:text-5xl">الأسئلة المتكررة</h2>
                <p class="mt-4 text-base font-normal leading-7 text-gray-600 lg:mt-6 lg:text-lg lg:leading-8"> إجابات سريعة لأكثر الأسئلة شيوعا حول الخدمات، المواعيد، والتسعير.</p>
            </div>

            <div class="mx-auto mt-12 max-w-5xl divide-y divide-gray-200 overflow-hidden rounded-xl border border-gray-200 sm:mt-16" x-data="{ active: 1 }">
                @foreach ($faqs as $faq)
                    <div wire:key="faq-{{ $loop->index }}" role="region">
                        <h3>
                            <button
                                type="button"
                                x-on:click="active = active === {{ $loop->iteration }} ? null : {{ $loop->iteration }}"
                                x-bind:aria-expanded="active === {{ $loop->iteration }}"
                                class="flex w-full items-center justify-between px-6 py-5 text-start text-lg font-semibold text-gray-900 sm:p-6"
                            >
                                <span>{{ $faq['question'] }}</span>
                                <span x-show="active === {{ $loop->iteration }}" aria-hidden="true" class="ml-4">
                                    <svg class="h-6 w-6 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </span>
                                <span x-show="active !== {{ $loop->iteration }}" aria-hidden="true" class="ml-4">
                                    <svg class="h-6 w-6 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                            </button>
                        </h3>

                        <div x-cloak x-show="active === {{ $loop->iteration }}" x-collapse>
                            <div class="px-6 pb-6">
                                <p class="text-base text-gray-600">{{ $faq['answer'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mx-auto mt-8 max-w-5xl overflow-hidden rounded-xl bg-gray-100 text-center sm:mt-12">
                <div class="px-6 py-12 sm:p-12">
                    <div class="mx-auto max-w-sm">
                        <div class="relative z-0 flex items-center justify-center -space-x-2 overflow-hidden">
                            <img class="relative z-10 inline-block h-14 w-14 rounded-full ring-4 ring-gray-100" src="https://landingfoliocom.imgix.net/store/collection/saasui/images/faq/1/avatar-male.png" alt="" />
                            <img class="relative z-30 inline-block h-16 w-16 rounded-full ring-4 ring-gray-100" src="https://landingfoliocom.imgix.net/store/collection/saasui/images/faq/1/avatar-female-1.png" alt="" />
                            <img class="relative z-10 inline-block h-14 w-14 rounded-full ring-4 ring-gray-100" src="https://landingfoliocom.imgix.net/store/collection/saasui/images/faq/1/avatar-female-2.png" alt="" />
                        </div>

                        <h3 class="mt-6 text-2xl font-semibold text-gray-900">Still have questions?</h3>
                        <p class="mt-2 text-base font-normal text-gray-600">Can't find the answer you're looking for? Please chat with our friendly team.</p>
                        <div class="mt-6">
                            <a
                                href="{{ route('tenant.pages.reviews') }}"
                                wire:navigate
                                class="inline-flex items-center justify-center rounded-full border border-transparent bg-blue-600 px-6 py-3 text-base font-medium text-white transition-all duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2"
                                role="button"
                            >
                                Contact support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-tenant::pages.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, array{question: string, answer: string}> */
    public array $faqs = [];

    public function mount(): void
    {
        $this->faqs = [
            [
                'question' => 'كيف أبدأ طلب خدمة التشطيب؟',
                'answer' => 'يمكنك بدء الطلب من صفحة الخدمات واختيار الخدمة المناسبة، ثم تعبئة بيانات التواصل والموقع. بعدها يتواصل معك الفريق لتأكيد المعاينة والخطوات التالية.',
            ],
            [
                'question' => 'هل تقدمون زيارة معاينة قبل التنفيذ؟',
                'answer' => 'نعم، نوفر معاينة مبدئية لتقييم المكان واحتياجات العمل بدقة، ثم نشاركك نطاق التنفيذ والمدة التقديرية قبل بدء المشروع.',
            ],
            [
                'question' => 'كم يستغرق تنفيذ المشروع عادة؟',
                'answer' => 'المدة تختلف حسب نوع التشطيب وحجم المساحة، لكن يتم تحديد جدول زمني واضح بعد المعاينة واعتماد خطة التنفيذ.',
            ],
            [
                'question' => 'هل يمكن تقسيط التكلفة على دفعات؟',
                'answer' => 'نعم، غالبا يتم تقسيم الدفعات على مراحل التنفيذ المتفق عليها في العقد، بحيث تكون واضحة ومرتبطة بالتقدم الفعلي في المشروع.',
            ],
            [
                'question' => 'هل تتوفر ضمانات على الأعمال المنفذة؟',
                'answer' => 'نعم، يتم توضيح الضمانات لكل بند حسب نوع الخدمة والخامات المستخدمة، مع دعم ما بعد التسليم لمعالجة الملاحظات.',
            ],
        ];
    }
};
?>
