<x-tenant-theme::layout width="max-w-3xl">

        @livewire('tenant.blocks.header')
        @livewire('tenant.blocks.cta')

        <section class="w-full mb-5 mt-10 flex flex-col gap-y-6">
            @foreach ($pageBlocks as $block)
                @switch($block->type)
                    @case('link')
                        <livewire:tenant.blocks.link :block-id="$block->id" :key="'page-block-'.$block->id" />
                        @break
                    @case('block-link')
                        <livewire:tenant.blocks.block-link :block-id="$block->id" :key="'page-block-'.$block->id" />
                        @break
                    @default
                        @includeFirst([
                            $block->variant,
                            "tenant-theme::blocks.{$block->type}",
                            "default-tenant-theme::blocks.{$block->type}",
                        ], ['block' => $block])
                @endswitch
            @endforeach

            {{-- <x-tenant-theme::hero /> --}}

            {{-- <x-tenant-theme::block-link title="المتجر" link="{{route('tenant.store.index')}}" icon="hugeicons:store-02" desc="مجموعة مختارة من تشطيبات ديكور المنزل، باركيه وبديل الرخام والخشب والشيبورد. " />
            <x-tenant-theme::block-link title="الدورات" link="{{route('tenant.courses.index')}}" icon="hugeicons:presentation-06" desc="دورات عملية لتعلم التشطيبات والديكور خطوة بخطوة مع تمارين وتطبيقات واقعية." />
            <x-tenant-theme::block-link title="قوائم التشغيل" link="{{route('tenant.playlists.index')}}" icon="hugeicons:playlist-01" desc="قوائم تشغيل فيديو وصوت مع تشغيل متتابع وسريع للمحتوى التعليمي." />
            <x-tenant-theme::block-link title="أعمالنا في التنفيذ" link="{{route('tenant.portfolio.index')}}" icon="hugeicons:folder-library" desc=" معرض مختار لأعمالنا المنفّذة في التشطيبات الداخلية، من الباركيه إلى بدائل الرخام والخشب. " />
            <x-tenant-theme::block-link title="قائمة الطعام" link="{{route('tenant.menu.index')}}" icon="hugeicons:restaurant-01" desc="قائمة وجبات طازجة مع أحجام وإضافات متنوعة، وأسرع طريقة لإضافتها إلى السلة." />
            <x-tenant-theme::block-link title="السيرة الذاتية" link="{{route('tenant.pages.resume')}}" icon="hugeicons:user-account" desc="تعرّف على خبراتي، المهارات، المشاريع المنفذة، وطرق التواصل المباشر في صفحة CV متكاملة." />
            <x-tenant-theme::block-link title="تحديثات المنصة" link="{{route('tenant.pages.changelog')}}" icon="hugeicons:note-01" desc="اطّلع على أحدث الإضافات والتحسينات والإصلاحات في كل إصدار، بتفاصيل واضحة وسريعة." />
            <x-tenant-theme::block-link title="المزايا" link="{{route('tenant.pages.features')}}" icon="hugeicons:magic-wand-02" desc="تعرّف على أهم المزايا التي تجعل تجربة الإدارة والتنفيذ أسرع وأكثر كفاءة." />
            <x-tenant-theme::block-link title="تأجير الوحدات" link="{{route('tenant.properties-rental.index')}}" icon="hugeicons:bed-double" desc="اختر وحدتك المناسبة من الاستديوهات والشقق، حدّد تاريخ الدخول والخروج واحجز مباشرة بسهولة." />
            <x-tenant-theme::block-link title="العقارات" link="{{route('tenant.properties.index')}}" icon="hugeicons:building-03" desc="تصفح عروض التأجير والبيع: شقق، فلل، وأراضٍ مع أسعار واضحة وتواصل مباشر مع المسوق." />
            <x-tenant-theme::block-link title="الباقات والأسعار" link="{{route('tenant.pages.pricing')}}" icon="hugeicons:credit-card-change" desc="اختر الباقة المناسبة لك مع مقارنة واضحة بين المزايا والأسعار." />
            <x-tenant-theme::block-link title="الأسئلة المتكررة" link="{{route('tenant.pages.faq')}}" icon="hugeicons:help-circle" desc="إجابات واضحة وسريعة لأكثر الأسئلة حول الخدمات، الأسعار، وآلية التنفيذ." />
            <x-tenant-theme::block-link title="اتصل بنا" link="{{route('tenant.pages.contact')}}" icon="hugeicons:call" desc="تواصل معنا عبر النموذج، الجوال، البريد، والواتساب مع روابط السوشال." /> --}}

            {{-- <x-tenant-theme::block-link title="النشرة البريدية" link="{{route('tenant.newsletter.index')}}" icon="hugeicons:mail-at-sign-02" desc=" اشترك في النشرة البريدية وانظم إلى مجتمع المهتمين في أعمال الفن والديكور. ">
                <div class="">
                    <form class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-grow group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <iconify-icon icon="hugeicons:mail-02" class="text-2xl text-stone-400" stroke-width="1.5"></iconify-icon>
                            </div>
                            <input type="email" placeholder="your@email.com" dir="ltr" class="custom-input font-semibold w-full rounded-xl border-2 border-transparent bg-stone-50 py-3 pl-11 pr-4 text-base text-stone-600 placeholder-stone-400 outline-none focus:border-stone-200 focus:bg-stone-100" required="">
                        </div>
                        <button type="button" class="group relative flex items-center justify-center gap-2 rounded-xl bg-primary-50 px-6 py-3 text-base font-medium text-primary-400 transition-all hover:bg-primary-100 hover:text-primary-500">
                            <span> اشترك الآن </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-left rotate-180 size-5 transition-transform hover:-translate-x-1"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        </button>
                    </form>
                    <p class="mt-3 text-xs text-stone-400">انضم إلى 2,400+ مجتمع المهتمين في أعمال الفن والديكور.</p>
                </div>
            </x-tenant-theme::block-link>             --}}

            {{-- <x-tenant-theme::block-link title="خدماتنا" link="{{route('tenant.services.index')}}" icon="hugeicons:travel-bag" desc="نقدم خدمات التشطيبات والديكور الداخلي باحترافية، من التصميم حتى التنفيذ بجودة عالية.">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4  w-full">

                    <!-- Item 1 -->
                    <div class="bg-stone-100 rounded-xl p-3 w-full">
                        <div class="flex items-start gap-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                <svg class="size-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium mb-1">تشطيب داخلي كامل</h5>
                                <p class="text-stone-500 text-xs">خطة تنفيذ متكاملة من البداية للتسليم.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="bg-stone-100 rounded-xl p-3 w-full">
                        <div class="flex items-start gap-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                <svg class="size-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium mb-1">تركيب باركيه فاخر</h5>
                                <p class="text-stone-500 text-xs">خامات مميزة مع تركيب احترافي.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="bg-stone-100 rounded-xl p-3 w-full">
                        <div class="flex items-start gap-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                <svg class="size-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium mb-1">بديل الرخام</h5>
                                <p class="text-stone-500 text-xs">حل عملي وأنيق للجدران والمداخل.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-tenant-theme::block-link> --}}


            {{-- <x-tenant-theme::block-link
                title="تقييمات العملاء"
                link="{{route('tenant.pages.reviews')}}"
                >

                <x-slot:icon>
                    <div class="flex -space-x-3x">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&amp;w=100&amp;auto=format&amp;fit=crop" class="size-12 rounded-full ring-2 ring-white object-cover" alt="">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&amp;w=100&amp;auto=format&amp;fit=crop" class="size-12 rounded-full ring-2 ring-white object-cover -mr-4" alt="">
                        <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?q=80&amp;w=100&amp;auto=format&amp;fit=crop" class="size-12 rounded-full ring-2 ring-white object-cover -mr-4" alt="">
                    </div>
                </x-slot>

                <x-slot:desc>
                    <div class="flex items-center gap-1 mt-0.5">
                        <div class="flex">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star size-4 fill-yellow-400 text-yellow-400"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star size-4 fill-yellow-400 text-yellow-400"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star size-4 fill-yellow-400 text-yellow-400"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star size-4 fill-yellow-400 text-yellow-400"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star size-4 fill-yellow-400 text-yellow-400"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                        </div>
                        <span class="text-sm md:text-base text-stone-400 ms-2">4.9 (12.5k تقييم)</span>
                    </div>
                </x-slot>

                <div class="flex gap-4 overflow-x-auto no-scrollbar">

                    <div class="md:w-1/3 w-1/2 shrink-0 rounded-2xl border border-stone-100 bg-stone-50 p-6">
                        <div class="mb-4 flex text-yellow-500">
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                        </div>
                        <p class="text-sm leading-relaxed text-stone-600">"The most hygienic and tasty food stop on NH34. The Mutton Kasha reminded me of home. Highly recommended for families."</p>
                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-stone-200"></div>
                            <div>
                                <p class="text-xs font-semibold text-stone-900">Anirban Das</p>
                                <p class="text-[10px] text-stone-500">Local Guide • Google Reviews</p>
                            </div>
                        </div>
                    </div>

                    <!-- Review 2 -->
                    <div class="md:w-1/3 w-1/2 shrink-0 rounded-2xl border border-stone-100 bg-stone-50 p-6">
                        <div class="mb-4 flex text-yellow-500">
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                        </div>
                        <p class="text-sm leading-relaxed text-stone-600">"Clean washrooms, ample parking, and the staff is so polite. The women-led initiative is wonderful to see. Great coffee too!"</p>
                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-stone-200"></div>
                            <div>
                                <p class="text-xs font-semibold text-stone-900">Priya Sen</p>
                                <p class="text-[10px] text-stone-500">TripAdvisor</p>
                            </div>
                        </div>
                    </div>

                    <!-- Review 3 -->
                    <div class="md:w-1/3 w-1/2 shrink-0 rounded-2xl border border-stone-100 bg-stone-50 p-6">
                        <div class="mb-4 flex text-yellow-500">
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-half-bold" width="16"></iconify-icon>
                        </div>
                        <p class="text-sm leading-relaxed text-stone-600">"Perfect pitstop. The thali system is very affordable and filling. Service was quick despite the crowd."</p>
                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-stone-200"></div>
                            <div>
                                <p class="text-xs font-semibold text-stone-900">Rahul M.</p>
                                <p class="text-[10px] text-stone-500">Zomato</p>
                            </div>
                        </div>
                    </div>

                    <!-- Review 4 -->
                    <div class="md:w-1/3 w-1/2 shrink-0 rounded-2xl border border-stone-100 bg-stone-50 p-6">
                        <div class="mb-4 flex text-yellow-500">
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" width="16"></iconify-icon>
                            <iconify-icon icon="solar:star-half-bold" width="16"></iconify-icon>
                        </div>
                        <p class="text-sm leading-relaxed text-stone-600">"Perfect pitstop. The thali system is very affordable and filling. Service was quick despite the crowd."</p>
                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-stone-200"></div>
                            <div>
                                <p class="text-xs font-semibold text-stone-900">Rahul M.</p>
                                <p class="text-[10px] text-stone-500">Zomato</p>
                            </div>
                        </div>
                    </div>

                </div>
            </x-tenant-theme::block-link> --}}



    </section>
</x-tenant-theme::layout>
