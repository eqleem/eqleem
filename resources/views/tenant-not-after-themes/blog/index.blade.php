<x-tenant::blog.layout>


    <section class="px-2 mb-8 w-full flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 w-full rounded-2xl p-1 whitespace-nowrap">
            <button class="p-3 text-center py-2.5 rounded-xl bg-white text-stone-900 text-sm font-medium shadow-sm">الكل</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">المقالات</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">شذرات</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900"> قصص قصيرة جداً </button>
        </div>
    
        <div class="flex items-center gap-3">
        <button class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="search" aria-hidden="true" class="lucide lucide-search size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
        </button>
        {{-- <button class="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="filter" aria-hidden="true" class="lucide lucide-filter w-5 h-5 text-stone-700"><path d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"></path></svg>
        </button> --}}
        </div> 
    </section>

 
    <section class="">
        <div class="space-y-5 md:space-y-6">
            <article class="group bg-stone-100/80 hover:bg-stone-200/50 rounded-2xl p-2 md:p-4">
                <a href="{{ route('tenant.blog.detail', 'guide-korea') }}" wire:navigate class="flex items-start gap-4 md:gap-6 ">
                    <img
                        src="https://r2-bucket.thmanyah.com/cdn-cgi/image/width=400/media/2026/158-20260616-V02.jpg"
                        alt="دليلك الأدبي لكوريا الجنوبية"
                        class="h-20 w-20 shrink-0 rounded-2xl object-cover md:h-28 md:w-28"
                    >

                    <div class="flex-1 ">
                        <h3 class="mb-2 text-base font-extrabold leading-tight text-stone-900 md:text-xl">
                            دليلك الأدبي لكوريا الجنوبية 🇰🇷
                        </h3>
                        <p class="mb-3 text-sm text-stone-500 md:text-base">زائد: الكتاب في الدراما الكورية 📺</p>

                        <p class="flex flex-wrap items-center  gap-2 text-sm text-stone-400 md:text-base">
                            <span class="text-base font-extrabold text-orange-600 md:text-lg">علي الصباح</span>
                            {{-- <span>في نشرة  </span> --}}
                            <span>· 17 يونيو 2026 🌻</span>
                        </p>
                    </div>
                </a>
            </article>

            <article class="group bg-stone-100/80 hover:bg-stone-200/50 rounded-2xl p-2 md:p-4">
                <a href="{{ route('tenant.blog.detail', 'where-is-riyadh-guide') }}" wire:navigate class="flex items-start gap-4 md:gap-6">
                    <img
                        src="https://images.unsplash.com/photo-1610016302534-6f67f1c968d8?auto=format&fit=crop&w=400&q=80"
                        alt="وين اختفت دليلة الرياض"
                        class="h-20 w-20 shrink-0 rounded-2xl object-cover md:h-28 md:w-28"
                    >

                    <div class="flex-1 ">
                        <h3 class="mb-3 text-base font-extrabold leading-tight text-stone-900 md:text-xl">
                            وين اختفت دليلة الرياض؟
                        </h3>
                        <p class="mb-3 text-sm leading-relaxed text-stone-500 md:text-base">
                            العقار ليس سلعة عادية، إنه مكان  ، وأحيانًا خوف من فوات الفرصة.
                        </p>

                        <p class="flex flex-wrap items-center  gap-2 text-sm text-stone-400 md:text-base">
                            <span class="text-base font-extrabold text-orange-600 md:text-lg">يارا المسفر ورهام الزعيبي</span>
                            {{-- <span>في دليلة الرياض </span> --}}
                            <span>· 16 يونيو 2026</span>
                        </p>
                    </div>
                </a>
            </article>

            <article class="group bg-stone-100/80 hover:bg-stone-200/50 rounded-2xl p-2 md:p-4">
                <a href="{{ route('tenant.blog.detail', 'riyadh-real-estate') }}" wire:navigate class="flex items-start gap-4 md:gap-6">
                    <img
                        src="https://r2-bucket.thmanyah.com/cdn-cgi/image/width=400/media/2026/154-20260512-V02.jpg"
                        alt="ما الذي يحدث في سوق عقار الرياض"
                        class="h-20 w-20 shrink-0 rounded-2xl object-cover md:h-28 md:w-28"
                    >

                    <div class="flex-1 ">
                        <h3 class="mb-3 text-base font-extrabold leading-tight text-stone-900 md:text-xl">
                            ما الذي يحدث في سوق عقار الرياض؟
                        </h3>
                        <p class="mb-3 text-sm leading-relaxed text-stone-500 md:text-base">
                            العقار ليس سلعة عادية، إنه مكان وزمن وموقع وذاكرة وتوقع وفرصة، وأحيانًا خوف من فوات الفرصة.
                        </p>

                        <p class="flex flex-wrap items-center  gap-2 text-sm text-stone-400 md:text-base">
                            <span class="text-base font-extrabold text-orange-600 md:text-lg">صالح القمري</span>
                            {{-- <span>في نشرة الصفحة الأخيرة من ثمانية</span> --}}
                            <span>· 16 يونيو 2026 🌻</span>
                        </p>
                    </div>
                </a>
            </article>
        </div>
    </section>
</x-tenant::blog.layout>

<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>