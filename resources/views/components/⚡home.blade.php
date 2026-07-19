<div class="min-h-screen relative  bg-stone-200 text-[#111111] antialiased overflow-x-hidden selection:bg-[#EAEAEA] selection:text-[#111111]">
 
    <nav class="fixedX top-0 w-full z-50    transition-all duration-300">
        <div class="max-w-7xl mx-auto px-3 md:px-2 xl:px-0 h-[4.5rem] md:h-[5rem] flex items-center justify-between">
            <a href="{{ route('home') }}" wire:navigate class="font-display text-xl font-medium tracking-tighter text-[#111111] flex items-center gap-2">
                <img class="w-auto h-8" src="{{ asset('assets/images/logo.webp') }}" alt="" />
            </a>
          
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center h-10 px-5 shrink-0 rounded-full bg-[#111111] text-white text-sm font-normal hover:bg-[#333333] transition-colors">
                        لوحة التحكم
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-right size-4 ms-3 group-hover:-translate-x-1 transition-transform -rotate-180"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>
                @else
                    <a href="{{ route('auth.register-login') }}" wire:navigate class="inline-flex items-center justify-center h-10 px-5 shrink-0 rounded-full bg-[#111111] text-white text-sm font-normal hover:bg-[#333333] transition-colors">
                        <span class="inline md:hidden"> تسجيل </span>
                        <span class="hidden md:inline">أنش صفحتي الآن </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-right size-4 ms-3 group-hover:-translate-x-1 transition-transform -rotate-180"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('auth.register-login') }}" wire:navigate class="inline-flex items-center justify-center p-2 px-4 rounded-full border border-black/10 text-[#111111] text-sm font-normal hover:bg-black/5 transition-colors w-full sm:w-auto gap-2 group">
                        دخول 
                        <iconify-icon noobserver icon="solar:arrow-left-linear" stroke-width="1.5" class=" transition-transform hidden md:block"></iconify-icon>
                    </a>
                @endauth
  
            </div>
        </div>
    </nav>

    {{-- Showcase hero: centered headline + phone + floating capabilities --}}
    <section class="relative w-full overflow-hidden pt-28 xpb-16x sm:pt-32x sm:pb-20x lg:pt-36x lg:pb-24x"  >
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-[18%] left-1/2 -translate-x-1/2 w-[min(90vw,42rem)] h-[min(90vw,42rem)] rounded-full bg-[radial-gradient(circle_at_center,rgba(227,95,38,0.08)_0%,transparent_68%)]"></div>
            <div class="absolute bottom-[8%] right-[12%] size-56 rounded-full bg-orange-200/20 blur-3xl"></div>
            <div class="absolute top-[30%] left-[8%] size-48 rounded-full bg-stone-300/40 blur-3xl"></div>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 flex flex-col items-center text-center mb-24">
            <div class="text-xs w-auto flex-shrink bg-stone-300/80 text-stone-600 px-4 py-1.5 rounded-full tracking-widest inline-flex items-center gap-2 backdrop-blur-sm">
                <span class="size-3 rounded-full bg-[#E35F26] animate-pulse -mx-1 me-1"></span>
                جاهزة للنشر خلال دقائق
            </div>

            <div class="flex flex-col items-center gap-7 mt-6">
                <h1 class="-mt-2 text-[3.25rem] md:text-[4rem] 2xl:text-[4.5rem] lg:leading-[1.4] leading-[1.5] tracking-tighter text-[#111111]">
                    <span class="hero-line block">
                        ابنِ
                        <span class="relative inline-block font-bold text-[#C94309]">
                            صفحة أعمال
                            <svg class="absolute -bottom-1 lg:-bottom-4 left-0 w-full" viewBox="0 0 120 12" fill="none" aria-hidden="true">
                                <path id="underline-showcase" d="M2 10 C 60 3, 80 3, 117 5" stroke="#C94309" stroke-width="2.5" stroke-linecap="round" style="stroke-dashoffset: 0px; stroke-dasharray: 114.391;"></path>
                            </svg>
                        </span>
                    </span>
                    <b class="font-bold"> تبيع عنّك،</b>
                    بدقائق.
                </h1>

                <p class="text-base md:text-lg text-stone-500 leading-relaxed max-w-[34rem] font-normal mx-auto">
                    أنشئ صفحة لأعمالك، تحوّل زوارك إلى عملاء،
                     تعرض منتجاتك وخدماتك،
                      وتستقبل الطلبات والحجوزات على مدار الساعة.
                </p>

                <div class="flex flex-col items-center">
                    <a href="{{ route('auth.register-login') }}" wire:navigate class="w-full md:w-auto inline-flex items-center justify-center px-8 py-4 rounded-full bg-gray-900 text-white text-lg font-medium hover:bg-gray-800 transition-all duration-300 group">
                        أنشئ صفحتي الآن -  مجاناً
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 ms-3 group-hover:-translate-x-1 transition-transform -rotate-180" aria-hidden="true"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>

                    <p class="mt-5 text-xs lg:text-sm text-stone-400 font-normal flex flex-wrap items-center justify-center gap-x-3 gap-y-2">
                        <span class="inline-flex items-center gap-0.5">
                            <iconify-icon noobserver icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-500 text-base"></iconify-icon>
                            مجانًا
                        </span>
                        <span class="inline-flex items-center gap-0.5">
                            <iconify-icon noobserver icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-500 text-base"></iconify-icon>
                            بدون خبرة تقنية
                        </span>
                        <span class="inline-flex items-center gap-0.5">
                            <iconify-icon noobserver icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-500 text-base"></iconify-icon>
                            جاهزة خلال دقائق
                        </span>
                    </p>
                    <div class="mt-6 flex items-center justify-center gap-4">
                        <div class="flex x-space-x-3">
                            <img class="size-12 -mx-2 rounded-full border-2 border-stone-200 object-cover bg-stone-500/20 rounded-fulll p-1" src="{{ asset('assets/images/clients/frst.webp') }}" alt="User avatar">
                            <img class="size-12 -mx-2 rounded-full border-2 border-stone-200 object-cover bg-stone-500/20 rounded-fulll p-1" src="{{ asset('assets/images/clients/baderatg.jpg') }}" alt="User avatar">
                            <img class="size-12 -mx-2 rounded-full border-2 border-stone-200 object-cover bg-stone-500/20 rounded-fulll p-1" src="{{ asset('assets/images/clients/crisp-burger.webp') }}" alt="User avatar">
                            <img class="size-12 -mx-2 rounded-full border-2 border-stone-200 object-cover bg-stone-500/20 rounded-fulll p-1" src="{{ asset('assets/images/clients/aswar.avif') }}" alt="User avatar">
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="flex items-center gap-1 text-yellow-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                            </div>
                            <span class="text-xs font-medium text-stone-500 mt-1">
                                <span class="text-gray-900">4.9/5</span> تقييمات أصحاب الأعمال
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Baseline gallery: equal-size shots revealed by different amounts --}}
        <div class="hero-shots relative bg-blackx mx-auto px-4 sm:px-6 flex items-end justify-center gap-2.5 sm:gap-4">
            @foreach ([
                ['title' => 'متجر أزياء',    'visible' => '.45', 'img' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=640&auto=format&fit=crop'],
                ['title' => 'مقهى مختص',    'visible' => '.65', 'img' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=640&auto=format&fit=crop'],
                ['title' => 'صالون عناية',  'visible' => '.90', 'img' => 'https://images.unsplash.com/photo-1522337660859-02fbefca4702?q=80&w=640&auto=format&fit=crop'],
                ['title' => 'مطعم برجر',    'visible' => '.55', 'img' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=640&auto=format&fit=crop'],
                ['title' => 'استوديو تصوير', 'visible' => '.75', 'img' => 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=640&auto=format&fit=crop'],
                ['title' => 'مساحة عمل',    'visible' => '.30', 'img' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=640&auto=format&fit=crop'],
                ['title' => 'حلويات فاخرة', 'visible' => '.60', 'img' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=640&auto=format&fit=crop'],
            ] as $i => $shot)
                <figure class="hero-shot group/shot relative min-w-0 flex-1 {{ $i >= 4 ? 'hidden md:block' : 'block' }}" style="--visible: {{ $shot['visible'] }}">
                    <figcaption class="hero-shot-caption absolute inset-x-0 ms-1 flex items-end gap-1 text-[10px] sm:text-xs font-medium text-stone-500 transition-all duration-[550ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover/shot:text-stone-800">
                        <span class="truncate">{{ $shot['title'] }}</span>
                        <iconify-icon noobserver icon="arcticons:emoji-arrow-pointing-rightwards-then-curving-downwards" class="shrink-0 text-base sm:text-lg -scale-x-100 {{ $i % 2 === 0 ? '-rotate-6' : 'rotate-3' }} translate-y-1 text-stone-400 transition-colors duration-300 group-hover/shot:text-[#C94309]" aria-hidden="true"></iconify-icon>
                    </figcaption>
                    <div class="hero-shot-frame absolute inset-x-0 bottom-0 overflow-hidden rounded-t-2xl sm:rounded-t-3xl bg-stone-300 ring-1 ring-black/5 shadow-[0_18px_40px_-26px_rgba(0,0,0,0.4)]">
                        <img
                            src="{{ $shot['img'] }}"
                            alt="{{ $shot['title'] }}"
                            loading="lazy"
                            decoding="async"
                            class="absolute inset-x-0 bottom-0 w-full object-cover"
                        />
                    </div>
                </figure>
            @endforeach
        </div>

        <style>
            .hero-shots { --shot-height: 22rem; --caption-gap: 1.75rem; }
            @media (max-width: 767px) {
                .hero-shots { --shot-height: 15rem; --caption-gap: 1.5rem; }
            }
            /* Each shot reserves the full height so growing the frame never shifts layout */
            .hero-shot {
                height: calc(var(--shot-height) + var(--caption-gap));
            }
            .hero-shot-frame {
                height: calc(var(--shot-height) * var(--visible));
                transition: height 0.55s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .hero-shot-frame img {
                height: var(--shot-height);
            }
            .hero-shot-caption {
                bottom: calc(var(--shot-height) * var(--visible));
            }
            .hero-shots .hero-shot:hover .hero-shot-frame {
                height: var(--shot-height);
            }
            .hero-shots .hero-shot:hover .hero-shot-caption {
                bottom: var(--shot-height);
            }
        </style>

        {{-- Stage: phone + orbiting capability cards --}}
        <div hidden class="relative mx-auto xmt-12 sm:mt-14x lg:mt-16x w-full max-w-5xl px-2 sm:px-6 min-h-[34rem] sm:min-h-[38rem] lg:min-h-[42rem]">
            {{-- Soft ground glow under phone --}}
            <div class="absolute left-1/2 bottom-[6%] -translate-x-1/2 w-[70%] max-w-md h-24 rounded-full bg-stone-400/15 blur-2xl pointer-events-none" aria-hidden="true"></div>

            {{-- Center phone --}}
            <div class="absolute top-1/2 left-1/2 z-10 w-[17.5rem] sm:w-[18.5rem] md:w-[19.5rem] -translate-x-1/2 -translate-y-[52%]">
                <div class="relative transition-transform duration-700 ease-out hover:-translate-y-2">
                    <div class="absolute -inset-8 rounded-[3rem] bg-stone-400/10 blur-2xl pointer-events-none" aria-hidden="true"></div>

                    <div class="relative rounded-[2.75rem] bg-gradient-to-b from-zinc-100 via-zinc-700 to-zinc-950 p-[0.48rem] shadow-[0_28px_60px_-24px_rgba(0,0,0,0.28)] animate-[showcase-float_7s_ease-in-out_infinite]">
                        <div class="absolute inset-[1px] rounded-[2.65rem] bg-zinc-900 pointer-events-none" aria-hidden="true"></div>

                        <span class="absolute -start-[3px] top-[22%] h-7 w-[3px] rounded-s-md bg-zinc-500" aria-hidden="true"></span>
                        <span class="absolute -start-[3px] top-[32%] h-12 w-[3px] rounded-s-md bg-zinc-500" aria-hidden="true"></span>
                        <span class="absolute -end-[3px] top-[28%] h-14 w-[3px] rounded-e-md bg-zinc-500" aria-hidden="true"></span>

                        <div class="relative overflow-hidden rounded-[2.3rem] bg-white aspect-[9/17.2] ring-1 ring-white/15">
                            <div class="absolute top-3 left-1/2 -translate-x-1/2 z-30 h-5 w-[30%] max-w-[6.25rem] rounded-full bg-zinc-950 shadow-inner" aria-hidden="true"></div>

                            {{-- Mini business page — full bleed (same as lower hero) --}}
                            <div class="absolute inset-0 overflow-hidden pt-9 px-3.5 pb-3 flex flex-col bg-white" dir="rtl">
                                {{-- Profile --}}
                                <div class="flex flex-col items-center text-center shrink-0">
                                    <div class="relative mb-2">
                                        <div class="size-[3.75rem] rounded-full overflow-hidden bg-stone-100 ring-[2.5px] ring-primary-500/15 shadow-sm">
                                            <img
                                                src="{{ asset('assets/images/clients/aswar.avif') }}"
                                                alt=""
                                                class="size-full object-cover"
                                                loading="eager"
                                                decoding="async"
                                            />
                                        </div>
                                        <span class="absolute -bottom-0.5 -end-0.5 size-[1.15rem] rounded-full bg-primary-500 text-white flex items-center justify-center ring-[1.5px] ring-white">
                                            <iconify-icon noobserver icon="solar:verified-check-bold" class="text-[10px]"></iconify-icon>
                                        </span>
                                    </div>

                                    <h3 class="text-[0.9rem] font-bold text-zinc-900 leading-none tracking-tight">استوديو أُفق</h3>
                                    <p class="mt-1.5 inline-flex items-center gap-0.5 text-[10px] text-zinc-400 leading-none">
                                        <iconify-icon noobserver icon="solar:map-point-bold" class="text-[11px] text-primary-500"></iconify-icon>
                                        الرياض
                                    </p>
                                    <p class="mt-2 text-[10px] leading-snug text-zinc-500">
                                        نصمّم وننفّذ مساحات أنيقة وعملية — ديكور، تشطيب، وتركيب باحتراف.
                                    </p>

                                    {{-- Socials --}}
                                    <div class="mt-2.5 flex items-center justify-center gap-1.5">
                                        @foreach ([
                                            'mdi:instagram',
                                            'mdi:snapchat',
                                            'mdi:youtube',
                                            'ri:twitter-x-fill',
                                        ] as $social)
                                            <a href="{{ route('auth.register-login') }}" wire:navigate class="size-7 rounded-lg bg-stone-100 text-zinc-500 hover:bg-stone-200 hover:text-zinc-800 flex items-center justify-center transition-colors" aria-label="سوشال">
                                                <iconify-icon noobserver icon="{{ $social }}" class="text-[13px]"></iconify-icon>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- CTAs --}}
                                <div class="mt-3 grid grid-cols-2 gap-1.5 shrink-0">
                                    <a href="{{ route('auth.register-login') }}" wire:navigate class="inline-flex items-center justify-center gap-1 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-[10px] font-semibold py-2.5 px-1.5 shadow-sm shadow-primary-500/20 transition-colors">
                                        <iconify-icon noobserver icon="hugeicons:calendar-03" class="text-xs"></iconify-icon>
                                        حجز موعد
                                    </a>
                                    <a href="{{ route('auth.register-login') }}" wire:navigate class="inline-flex items-center justify-center gap-1 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white text-[10px] font-semibold py-2.5 px-1.5 transition-colors">
                                        <iconify-icon noobserver icon="mdi:whatsapp" class="text-sm"></iconify-icon>
                                        تواصل الآن
                                    </a>
                                </div>

                                {{-- Section links --}}
                                <div class="mt-2.5 space-y-1 flex-1 min-h-0">
                                    @foreach ([
                                        ['icon' => 'hugeicons:shopping-bag-01', 'title' => 'المتجر', 'desc' => 'منتجات وتشطيبات جاهزة'],
                                        ['icon' => 'hugeicons:note-edit', 'title' => 'الخدمات', 'desc' => 'تصميم · تنفيذ · استشارة'],
                                        ['icon' => 'hugeicons:image-01', 'title' => 'أعمالنا', 'desc' => 'معرض مشاريع منفّذة'],
                                        ['icon' => 'hugeicons:package-01', 'title' => 'الباقات', 'desc' => 'باقات تناسب ميزانيتك'],
                                    ] as $link)
                                        <a href="{{ route('auth.register-login') }}" wire:navigate class="group flex items-center gap-2 rounded-xl bg-stone-50 hover:bg-stone-100 px-2 py-1.5 transition-colors">
                                            <span class="size-7 rounded-lg bg-primary-500 text-white flex items-center justify-center shrink-0">
                                                <iconify-icon noobserver icon="{{ $link['icon'] }}" class="text-xs"></iconify-icon>
                                            </span>
                                            <span class="min-w-0 flex-1 text-start">
                                                <span class="block text-[11px] font-semibold text-zinc-900 leading-tight">{{ $link['title'] }}</span>
                                                <span class="block text-[9px] text-zinc-400 truncate leading-tight mt-0.5">{{ $link['desc'] }}</span>
                                            </span>
                                            <iconify-icon noobserver icon="solar:alt-arrow-left-linear" class="text-zinc-300 text-sm shrink-0 group-hover:text-zinc-500 transition-colors"></iconify-icon>
                                        </a>
                                    @endforeach
                                </div>

                                {{-- Soft conversion cue --}}
                                <a href="{{ route('auth.register-login') }}" wire:navigate class="mt-1.5 shrink-0 inline-flex items-center justify-center gap-1 text-[9px] font-medium text-primary-600 hover:text-primary-700">
                                    أنشئ صفحتك مثل هذه مجاناً
                                    <iconify-icon noobserver icon="solar:arrow-left-linear" class="text-[10px]"></iconify-icon>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Orbit: top-start — new order (from old hero) --}}
            <div class="absolute top-[2%] start-[2%] sm:start-[6%] lg:start-[4%] z-20 scale-75 origin-top-start sm:scale-100 transition-transform">
                <div
                    class="w-[12rem] sm:w-[14rem] rounded-2xl bg-sky-50/95 backdrop-blur-xl border border-sky-100/80 p-3.5 sm:p-4 shadow-[0_16px_40px_-18px_rgba(14,165,233,0.22)] animate-[showcase-float_5.5s_ease-in-out_infinite]"
                    style="animation-delay: -1.1s;"
                >
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-sky-700/80">
                            <span class="relative flex size-2">
                                <span class="absolute inline-flex size-full animate-ping rounded-full bg-sky-400/70 opacity-75"></span>
                                <span class="relative inline-flex size-2 rounded-full bg-sky-400"></span>
                            </span>
                            طلب جديد
                        </span>
                        <span class="text-[10px] text-sky-600/50">الآن</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="size-11 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                            <iconify-icon noobserver icon="hugeicons:shopping-bag-01" class="text-xl"></iconify-icon>
                        </div>
                        <div class="min-w-0 text-start">
                            <p class="text-sm font-semibold text-sky-950/80 truncate">باقة التصميم</p>
                            <p class="text-xs text-sky-700/55 mt-0.5 tabular-nums">٤٩٠ ر.س · مدفوع</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Keep: shipping status pill (mid-start) --}}
            <div class="absolute top-[28%] start-0 sm:start-[2%] lg:start-0 z-20 hidden md:block animate-[showcase-float_6.4s_ease-in-out_infinite]" style="animation-delay: -2.4s;">
                <div class="inline-flex items-center gap-2 rounded-full bg-sky-50 text-sky-800 px-3.5 py-2 shadow-[0_10px_28px_-10px_rgba(14,165,233,0.35)] border border-sky-100/80">
                    <iconify-icon noobserver icon="solar:delivery-bold" class="text-base text-sky-600"></iconify-icon>
                    <span class="text-xs font-medium">الشحنة في الطريق</span>
                </div>
            </div>

            {{-- Orbit: bottom-start — visitors (from old hero) --}}
            <div class="absolute bottom-[8%] start-[1%] sm:start-[5%] lg:start-[2%] z-20 scale-75 origin-bottom-start sm:scale-100 transition-transform">
                <div
                    class="rounded-2xl bg-teal-50/95 backdrop-blur-xl border border-teal-100/80 px-4 py-3 shadow-[0_16px_40px_-18px_rgba(20,184,166,0.2)] animate-[showcase-float_6.5s_ease-in-out_infinite]"
                    style="animation-delay: -3.2s;"
                >
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                            <iconify-icon noobserver icon="hugeicons:analytics-up" class="text-lg"></iconify-icon>
                        </div>
                        <div class="text-start">
                            <p class="text-[11px] text-teal-700/50 leading-none">زوار الآن</p>
                            <p class="text-sm font-semibold text-teal-950/80 mt-1 tabular-nums">١٢+</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Orbit: top-end — rating (from old hero) --}}
            <div class="absolute top-[4%] end-[1%] sm:end-[5%] lg:end-[3%] z-20 scale-75 origin-top-end sm:scale-100 transition-transform">
                <div
                    class="rounded-2xl bg-amber-50/95 backdrop-blur-xl border border-amber-100/80 px-4 py-3.5 shadow-[0_16px_40px_-18px_rgba(245,158,11,0.2)] animate-[showcase-float_6.2s_ease-in-out_infinite]"
                    style="animation-delay: -2.6s;"
                >
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                            <iconify-icon noobserver icon="solar:star-bold" class="text-xl"></iconify-icon>
                        </div>
                        <div class="text-start">
                            <p class="text-base font-semibold leading-none text-amber-950/80">٤.٩</p>
                            <p class="text-[11px] text-amber-700/55 mt-1.5">تقييم العملاء</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Keep: payment success (mid-end) --}}
            <div class="absolute top-[32%] end-0 sm:end-[1%] lg:end-0 z-20 hidden md:block animate-[showcase-float_5.2s_ease-in-out_infinite]" style="animation-delay: -1.8s;">
                <div class="flex items-center gap-2.5 rounded-[1.15rem] bg-white px-3.5 py-2.5 shadow-[0_12px_36px_-12px_rgba(0,0,0,0.18)] border border-black/[0.03]">
                    <span class="size-9 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <iconify-icon noobserver icon="solar:check-circle-bold" class="text-lg"></iconify-icon>
                    </span>
                    <div class="text-start pe-1">
                        <p class="text-xs font-semibold text-zinc-900">تم الدفع بنجاح</p>
                        <p class="text-[10px] text-zinc-400 mt-0.5 tabular-nums">عبر مدى · ٣٢٠ ر.س</p>
                    </div>
                </div>
            </div>

            {{-- Orbit: mid-end — booking (from old hero) --}}
            <div class="absolute top-[48%] end-[1%] sm:end-[4%] lg:end-[6%] z-20 scale-75 origin-right sm:scale-100 transition-transform">
                <div
                    class="w-[12rem] sm:w-[13.5rem] rounded-2xl bg-orange-50/95 backdrop-blur-xl border border-orange-100/80 p-3.5 sm:p-4 shadow-[0_16px_40px_-18px_rgba(249,115,22,0.18)] animate-[showcase-float_5.8s_ease-in-out_infinite]"
                    style="animation-delay: -0.4s;"
                >
                    <div class="flex items-start gap-3">
                        <div class="size-11 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                            <iconify-icon noobserver icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                        </div>
                        <div class="min-w-0 text-start">
                            <p class="text-sm font-semibold text-orange-950/80">حجز موعد</p>
                            <p class="text-xs text-orange-700/55 mt-0.5">غداً · ٤:٣٠ م</p>
                            <span class="mt-2 inline-flex items-center rounded-full bg-orange-100/80 text-orange-700/80 px-2 py-0.5 text-[10px] font-medium">مؤكد</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Orbit: bottom-end — contact (from old hero) --}}
            <div class="absolute bottom-[6%] end-[4%] sm:end-[10%] lg:end-[14%] z-20 scale-75 origin-bottom-end sm:scale-100 transition-transform">
                <div
                    class="rounded-2xl bg-emerald-50/95 backdrop-blur-xl border border-emerald-100/80 px-4 py-3 shadow-[0_16px_40px_-18px_rgba(16,185,129,0.2)] animate-[showcase-float_5.2s_ease-in-out_infinite]"
                    style="animation-delay: -1.8s;"
                >
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <iconify-icon noobserver icon="mdi:whatsapp" class="text-xl"></iconify-icon>
                        </div>
                        <div class="text-start">
                            <p class="text-sm font-semibold text-emerald-950/80">تواصل مباشر</p>
                            <p class="text-[11px] text-emerald-700/50 mt-0.5">واتساب · رد فوري</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @keyframes showcase-float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            @media (max-width: 639px) {
                @keyframes showcase-float {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-6px); }
                }
            }
        </style>
    </section>

    <section class="hidden relative w-full px-4 sm:px-6 flex items-center min-h-[calc(100vh-9rem)] pb-12 pt-16 lg:py-16 overflow-x-hidden">
        <div class="glow-bg top-[8%] left-[50%] -translate-x-[50%]"></div>

        <div class="max-w-7xl mx-auto w-full grid lg:grid-cols-12 gap-12 lg:gap-6 xl:gap-10 items-center">
            {{-- Copy --}}
            <div class="lg:col-span-6 order-1 relative z-10">
                <div class="text-xs w-auto flex-shrink bg-stone-300/80 text-stone-600 px-4 py-1.5 rounded-full tracking-widest inline-flex items-center gap-2 backdrop-blur-sm">
                    <span class="size-3 rounded-full bg-[#E35F26] animate-pulse -mx-1 me-1"></span>
                    جاهزة للنشر خلال دقائق
                </div>

                <div class="flex flex-col gap-7 mt-6">
                    <h1 class="-mt-2 text-[3.25rem] md:text-[4rem] 2xl:text-[4.5rem] lg:leading-[1.4] leading-[1.5] tracking-tighter text-[#111111]">
                        <span class="hero-line block">
                            ابنِ
                            <span class="relative inline-block text-[#C94309]">
                                صفحة أعمال
                                <svg class="absolute -bottom-1 lg:-bottom-4 left-0 w-full" viewBox="0 0 120 12" fill="none" aria-hidden="true">
                                    <path id="underline" d="M2 10 C 60 3, 80 3, 117 5" stroke="#C94309" stroke-width="2.5" stroke-linecap="round" style="stroke-dashoffset: 0px; stroke-dasharray: 114.391;"></path>
                                </svg>
                            </span>
                        </span>
                        <b class="font-bold"> تبيع عنّك،</b>
                        بدقائق.
                    </h1>

                    <p class="text-base md:text-lg text-stone-500 leading-relaxed max-w-[34rem] font-normal">
                        أنشئ صفحة لأعمالك، تستقبل الزوار، تجيب عن أسئلتهم، تعرض المنتجات والخدمات، وتستقبل الطلبات والحجوزات على مدار الساعة.
                    </p>

                    <div>
                        <a href="{{ route('auth.register-login') }}" wire:navigate class="w-full md:w-auto inline-flex items-center justify-center px-8 py-4 rounded-full bg-gray-900 text-white text-lg font-medium hover:bg-gray-800 transition-all duration-300 group">
                            أنشئ صفحتي الآن -  مجاناً
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 ms-3 group-hover:-translate-x-1 transition-transform -rotate-180" aria-hidden="true"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        </a>

                        <p class="mt-5 text-xs lg:text-sm text-stone-400 font-normal flex flex-wrap items-center gap-x-3 gap-y-2">
                            <span class="inline-flex items-center gap-0.5">
                                <iconify-icon noobserver icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-500 text-base"></iconify-icon>
                                مجانًا
                            </span>
                            <span class="inline-flex items-center gap-0.5">
                                <iconify-icon noobserver icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-500 text-base"></iconify-icon>
                                بدون خبرة تقنية
                            </span>
                            <span class="inline-flex items-center gap-0.5">
                                <iconify-icon noobserver icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-500 text-base"></iconify-icon>
                                جاهزة خلال دقائق
                            </span>
                        </p>
                        <div class="mt-6 flex items-center gap-4">
                            <div class="flex x-space-x-3">
                                <img class="size-12 -mx-2 rounded-full border-2 border-stone-200 object-cover bg-stone-500/20 rounded-fulll p-1" src="{{ asset('assets/images/clients/frst.webp') }}" alt="User avatar">
                                <img class="size-12 -mx-2 rounded-full border-2 border-stone-200 object-cover bg-stone-500/20 rounded-fulll p-1" src="{{ asset('assets/images/clients/baderatg.jpg') }}" alt="User avatar">
                                <img class="size-12 -mx-2 rounded-full border-2 border-stone-200 object-cover bg-stone-500/20 rounded-fulll p-1" src="{{ asset('assets/images/clients/crisp-burger.webp') }}" alt="User avatar">
                                <img class="size-12 -mx-2 rounded-full border-2 border-stone-200 object-cover bg-stone-500/20 rounded-fulll p-1" src="{{ asset('assets/images/clients/aswar.avif') }}" alt="User avatar">
                            </div>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1 text-yellow-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" aria-hidden="true" class="lucide lucide-star w-4 h-4 fill-current"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                </div>
                                <span class="text-xs font-medium text-stone-500 mt-1">
                                    <span class="text-gray-900">4.9/5</span> تقييمات أصحاب الأعمال
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 
        </div>

        <style>
            @keyframes home-float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-12px); }
            }
            @media (max-width: 1023px) {
                @keyframes home-float {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-7px); }
                }
            }
        </style>
    </section>



    <div class="hidden gridx grid-cols-2x md:grid-cols-7x overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] flex itmes-center justify-center bg-[#0B161B] text-white border-t border-[#262626]">
        <div class="w-full border-r border-[#262626] p-6 flex flex-col gap-8 group hover:bg-[#141414] hover:text-[#F97316] transition-colors cursor-pointer">
          <div class="flex justify-between items-start">
            <iconify-icon noobserver icon="solar:bed-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
            <span class="text-3xl font-extralight opacity-20 group-hover:opacity-100 transition-opacity font-mono">
              01
            </span>
          </div>
          <div class="">
            <span class="text-xs font-light opacity-50 block mb-1">//</span>
            <h4 class="font-medium uppercase text-sm group-hover:text-white">
              PRIME
              <br>
              REST
            </h4>
          </div>
        </div>
        <div class="w-full border-r border-[#262626] p-6 flex flex-col gap-8 group hover:bg-[#141414] hover:text-[#F97316] transition-colors cursor-pointer">
          <div class="flex justify-between items-start">
            <iconify-icon noobserver icon="solar:cup-hot-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
            <span class="text-3xl font-extralight opacity-20 group-hover:opacity-100 transition-opacity font-mono">
              02
            </span>
          </div>
          <div class="">
            <span class="text-xs font-light opacity-50 block mb-1">//</span>
            <h4 class="font-medium uppercase text-sm group-hover:text-white">
              ARTISAN
              <br>
              ROASTS
            </h4>
          </div>
        </div>
        <div class="w-full border-r border-[#262626] p-6 flex flex-col gap-8 group hover:bg-[#141414] hover:text-[#F97316] transition-colors cursor-pointer hidden md:flex">
          <div class="flex justify-between items-start">
            <iconify-icon noobserver icon="solar:dumbbell-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
            <span class="text-3xl font-extralight opacity-20 group-hover:opacity-100 transition-opacity font-mono">
              03
            </span>
          </div>
          <div class="">
            <span class="text-xs font-light opacity-50 block mb-1">//</span>
            <h4 class="font-medium uppercase text-sm group-hover:text-white">
              KINETIC
              <br>
              ZONES
            </h4>
          </div>
        </div>
        <div class="w-full border-r border-[#262626] p-6 flex flex-col gap-8 group hover:bg-[#141414] hover:text-[#F97316] transition-colors cursor-pointer hidden md:flex">
          <div class="flex justify-between items-start">
            <iconify-icon noobserver icon="solar:camera-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
            <span class="text-3xl font-extralight opacity-20 group-hover:opacity-100 transition-opacity font-mono">
              04
            </span>
          </div>
          <div>
            <span class="text-xs font-light opacity-50 block mb-1">//</span>
            <h4 class="font-medium uppercase text-sm group-hover:text-white">
              MEDIA
              <br>
              LABS
            </h4>
          </div>
        </div>
        <div class="w-full border-e border-[#262626] p-6 flex flex-col gap-8 group hover:bg-[#141414] hover:text-[#F97316] transition-colors cursor-pointer hidden md:flex">
          <div class="flex justify-between items-start">
            <iconify-icon noobserver icon="solar:wineglass-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
            <span class="text-3xl font-extralight opacity-20 group-hover:opacity-100 transition-opacity font-mono">
              05
            </span>
          </div>
          <div>
            <span class="text-xs font-light opacity-50 block mb-1">//</span>
            <h4 class="font-medium uppercase text-sm group-hover:text-white">
              EVENING
              <br>
              MIXERS
            </h4>
          </div>
        </div>
        <div class="w-full  p-6 flex flex-col gap-8 group hover:bg-[#141414] hover:text-[#F97316] transition-colors cursor-pointer hidden md:flex">
          <div class="flex justify-between items-start">
            <iconify-icon noobserver icon="solar:medal-star-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
            <span class="text-3xl font-extralight opacity-20 group-hover:opacity-100 transition-opacity font-mono">
              06
            </span>
          </div>
          <div>
            <span class="text-xs font-light opacity-50 block mb-1">//</span>
            <h4 class="font-medium uppercase text-sm group-hover:text-white">
              ELITE
              <br>
              ACCESS
            </h4>
          </div>
        </div>        
        <div class="w-full    p-6 flex flex-col gap-8 group hover:bg-[#141414] hover:text-[#F97316] transition-colors cursor-pointer hidden md:flex">
          <div class="flex justify-between items-start">
            <iconify-icon noobserver icon="solar:medal-star-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
            <span class="text-3xl font-extralight opacity-20 group-hover:opacity-100 transition-opacity font-mono">
              07
            </span>
          </div>
          <div>
            <span class="text-xs font-light opacity-50 block mb-1">//</span>
            <h4 class="font-medium uppercase text-sm group-hover:text-white">
              ELITE
              <br>
              ACCESS
            </h4>
          </div>
        </div>
      </div>
 

    <div class="hidden w-full border-y border-black/5 bg-white py-5 reveal-up active ">
        <div class="mx-auto flex max-w-5xlx items-center justify-center gap-x-8 gap-y-4 opacity-30 hover:opacity-100 transition-opacity duration-300 px-6 sm:gap-x-10 overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
            <img src="{{ asset('assets/images/partners/tabby.svg') }}" alt="تابي" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/tamara.svg') }}" alt="تمارا" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/mada.svg') }}" alt="مدى" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/aramex.svg') }}" alt="أرامكس" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/fedex.svg') }}" alt="فيديكس" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/visa.svg') }}" alt="فيزا" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/mastercard.svg') }}" alt="ماستركارد" class="h-6 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-7" />
            <img src="{{ asset('assets/images/partners/tabby.svg') }}" alt="تابي" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/tamara.svg') }}" alt="تمارا" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/mada.svg') }}" alt="مدى" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/aramex.svg') }}" alt="أرامكس" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/fedex.svg') }}" alt="فيديكس" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/visa.svg') }}" alt="فيزا" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/mastercard.svg') }}" alt="ماستركارد" class="h-6 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-7" />
                   <img src="{{ asset('assets/images/partners/mastercard.svg') }}" alt="ماستركارد" class="h-6 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-7" />
            <img src="{{ asset('assets/images/partners/tabby.svg') }}" alt="تابي" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/tamara.svg') }}" alt="تمارا" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/mada.svg') }}" alt="مدى" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/aramex.svg') }}" alt="أرامكس" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/fedex.svg') }}" alt="فيديكس" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/visa.svg') }}" alt="فيزا" class="h-5 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-6" />
            <img src="{{ asset('assets/images/partners/mastercard.svg') }}" alt="ماستركارد" class="h-6 grayscale hover:grayscale-0 w-auto opacity-80 transition-opacity hover:opacity-100 sm:h-7" />
       
        </div>
    </div>


    <section class="bg-white border-b border-gray-100 py-16 lg:py-40 px-3 overflow-hidden">
        <div class="max-w-7xl mx-auto relative">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-8 items-center">

              <div class="lg:col-span-7 order-1">
                <p data-reveal="" class="text-4xl font-thin tracking-widest text-stone-300 flex items-center gap-2 mb-4">
                    <span class="w-6 h-px bg-stone-300"></span>
                    01
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl  lg:!leading-[4rem] max-w-md lg:max-w-2xl tracking-tight">
                    صفحة تحوّل
                    <b class="font-bold"> زوارك  إلى عملاء،</b>
                     حتى وأنت نائم.
                </h2>

                <div class="max-w-2xl mt-8">
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        حوّل كل زيارة إلى طلب، أو حجز، أو عملية شراء، عبر تجربة واضحة تقود العميل إلى الإجراء المناسب.
                        
                        {{-- اجعل كل زيارة فرصة للبيع أو الحجز أو التواصل، من خلال تجربة واضحة تقود العميل إلى اتخاذ الإجراء المناسب دون تشتت. --}}
                    </p>

                    <div class="flex flex-wrap gap-1.5 lg:gap-2 mt-10 mb-10 lg:mb-2 max-w-lg lg:max-w-2xl">
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80  transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:shopping-cart-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm lg:text-sm ">متجر إلكتروني</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 xborder border-black/10 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:calendar-03" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm lg:text-sm">حجز المواعيد</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2   rounded-full flex items-center gap-1.5 lg:gap-2 bg-primary-500 text-white  hover:bg-primary-600 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:note-edit" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm lg:text-sm">طلب خدمة</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 xborder border-black/10 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:credit-card" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm lg:text-sm">الدفع الإلكتروني</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 xborder border-black/10 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:package-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-xs lg:text-sm">المنتجات الرقمية</span>
                        </div>
                        <div class="flex px-3 py-1.5 lg:px-4 lg:py-2 xborder border-black/10 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:gift" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm lg:text-sm">تأجير الوحدات</span>
                        </div>
                        <div class="flex px-3 py-1.5 lg:px-4 lg:py-2 xborder border-black/10 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:call" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm lg:text-sm">تواصل مباشر</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 xborder border-black/10 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:target-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm lg:text-sm font-bold">زر "احجز الآن"</span>
                        </div>
                        
                    </div>
                </div>
              </div>

              {{-- Floating order card: visual left on desktop (RTL), after copy on mobile --}}
              <div class="lg:col-span-5 order-2 flex justify-center lg:justify-end pt-6 pb-8 lg:py-0">
                <div class="relative w-full max-w-sm mx-auto lg:mx-0 lg:-rotate-2 lg:hover:rotate-0 transition-transform duration-500">
                    {{-- Soft glow --}}
                    <div class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-primary-500/15 via-amber-200/20 to-transparent blur-2xl pointer-events-none" aria-hidden="true"></div>

                    {{-- Floating toast --}}
                    <div
                        class="absolute -top-3 start-3 z-20 inline-flex items-center gap-2 rounded-full bg-zinc-900 text-white px-3 py-1.5 text-xs font-medium shadow-lg shadow-zinc-900/20 animate-[home-float_5s_ease-in-out_infinite]"
                        style="animation-delay: -1.5s;"
                    >
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span>
                        </span>
                        طلب جديد الآن
                    </div>

                    {{-- Main card --}}
                    <div class="relative rounded-3xl bg-white/90 backdrop-blur-xl border border-black/5 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.18)] p-5 sm:p-6 animate-[home-float_6s_ease-in-out_infinite]">
                        <div class="flex items-start justify-between gap-3 mb-5">
                            <div>
                                <p class="text-[11px] tracking-widest text-zinc-400 uppercase">طلب #2841</p>
                                <h3 class="text-lg font-semibold text-zinc-900 mt-0.5">طلب جديد</h3>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-1 text-xs font-medium ring-1 ring-emerald-600/10">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                مدفوع
                            </span>
                        </div>

                        <div class="flex items-center gap-3 rounded-2xl bg-stone-50 p-3 ring-1 ring-black/5">
                            <div class="size-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-primary-500/25">
                                <iconify-icon noobserver icon="hugeicons:shopping-bag-01" class="text-xl"></iconify-icon>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-zinc-900 truncate">باقة التصميم الاحترافي</p>
                                <p class="text-xs text-zinc-500 mt-0.5">سارة الأحمد · الرياض</p>
                            </div>
                            <p class="text-sm font-semibold text-zinc-900 shrink-0 tabular-nums">٤٩٠ ر.س</p>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-zinc-500">حالة الطلب</span>
                                <span class="font-medium text-zinc-800">قيد التجهيز</span>
                            </div>

                            {{-- Progress with status icons at start / middle / end --}}
                            <div class="relative pt-1 pb-6">
                                <div class="absolute inset-x-3.5 top-[1.125rem] h-1.5 rounded-full bg-stone-100">
                                    <div class="h-full w-[50%] rounded-full bg-gradient-to-l from-primary-500 to-primary-400"></div>
                                </div>
                                <div class="relative flex items-start justify-between">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="size-7 rounded-full bg-sky-100 text-sky-700 ring-2 ring-white shadow-sm flex items-center justify-center">
                                            <iconify-icon noobserver icon="hugeicons:credit-card" class="text-sm"></iconify-icon>
                                        </span>
                                        <span class="text-[10px] text-zinc-500">مدفوع</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="size-7 rounded-full bg-primary-500 text-white ring-2 ring-white shadow-sm shadow-primary-500/30 flex items-center justify-center">
                                            <iconify-icon noobserver icon="hugeicons:package-01" class="text-sm"></iconify-icon>
                                        </span>
                                        <span class="text-[10px] font-medium text-primary-600">تجهيز</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="size-7 rounded-full bg-stone-100 text-stone-400 ring-2 ring-white flex items-center justify-center">
                                            <iconify-icon noobserver icon="hugeicons:tick-02" class="text-sm"></iconify-icon>
                                        </span>
                                        <span class="text-[10px] text-zinc-400">مكتمل</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <span class="text-[11px] text-zinc-400">منذ دقيقتين</span>
                            </div>
                        </div>
                    </div>

                    {{-- Secondary floating chip --}}
                    <div
                        class="absolute -bottom-4 end-2 sm:end-6 z-20 inline-flex items-center gap-2 rounded-2xl bg-white border border-black/5 px-3 py-2 shadow-lg shadow-black/10 animate-[home-float_5.5s_ease-in-out_infinite]"
                        style="animation-delay: -3s;"
                    >
                        <span class="size-8 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center">
                            <iconify-icon noobserver icon="hugeicons:notification-01" class="text-base"></iconify-icon>
                        </span>
                        <div class="leading-tight">
                            <p class="text-xs font-medium text-zinc-900">تم استلام الدفع</p>
                            <p class="text-[10px] text-zinc-400">تلقائياً · بدون تدخل</p>
                        </div>
                    </div>
                </div>
              </div>

            </div>
        </div>

        <style>
            @keyframes home-float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            @media (max-width: 1023px) {
                @keyframes home-float {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-6px); }
                }
            }
        </style>
    </section>


    

    <section class="bg-white/80 border-b border-gray-100 py-16 lg:py-40 px-3 overflow-hidden">
        <div class="max-w-7xl mx-auto relative">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-8 items-center">

              <div class="lg:col-span-7 order-1">
                <p data-reveal="" class="text-4xl font-thin tracking-widest text-stone-300 flex items-center gap-2 mb-4">
                    <span class="w-6 h-px bg-stone-300"></span>
                    02
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl lg:!leading-[4rem] max-w-md lg:max-w-2xl tracking-tight">
                    صفحة
                    <b class="font-bold"> ترسّخ علامتك التجارية</b>.
                </h2>

                <div class="max-w-2xl mt-8">
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        أنشئ صفحة تحمل هويتك بالكامل، من الدومين والألوان إلى الشعار وطريقة العرض، لتبدو امتدادًا طبيعيًا لعلامتك في كل زيارة.
                    </p>

                    <div class="flex flex-wrap gap-1.5 lg:gap-2 mt-10 mb-10 lg:mb-2 max-w-lg lg:max-w-2xl">
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-primary-500 text-white hover:bg-primary-600 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:globe" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">دومين مخصص</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:mail-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">بريد إلكتروني رسمي</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:paint-board" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">ألوان هويتك</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:text-font" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">الخطوط والشعار</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:smart-phone-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">تصميم متوافق مع الجوال</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:translate" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">دعم لغات متعددة</span>
                        </div>
                        <div class="hidden lg:flex px-3 py-1.5 lg:px-4 lg:py-2 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:unavailable" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">بدون شعار المنصة</span>
                        </div>
                        <div class="hidden lg:flex px-3 py-1.5 lg:px-4 lg:py-2 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:image-02" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">صور أغلفة مخصصة</span>
                        </div>
                    </div>
                </div>
              </div>

              {{-- Floating brand browser: visual left on desktop (RTL), after copy on mobile --}}
              <div class="lg:col-span-5 order-2 flex justify-center lg:justify-end pt-6 pb-8 lg:py-0">
                <div class="relative w-full max-w-sm mx-auto lg:mx-0 lg:rotate-2 lg:hover:rotate-0 transition-transform duration-500">
                    <div class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-primary-500/15 via-orange-200/25 to-transparent blur-2xl pointer-events-none" aria-hidden="true"></div>

                    {{-- Floating brand toast --}}
                    <div
                        class="absolute -top-3 start-2 z-20 inline-flex items-center gap-2 rounded-full bg-white border border-black/5 text-zinc-800 px-3 py-1.5 text-xs font-medium shadow-lg shadow-black/10 animate-[home-float_5s_ease-in-out_infinite]"
                        style="animation-delay: -1.2s;"
                    >
                        <span class="size-6 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center">
                            <iconify-icon noobserver icon="hugeicons:paint-board" class="text-sm"></iconify-icon>
                        </span>
                        تم تطبيق هوية علامتك
                    </div>

                    {{-- Mini browser --}}
                    <div class="relative rounded-2xl bg-white border border-black/5 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.18)] overflow-hidden animate-[home-float_6s_ease-in-out_infinite]">
                        {{-- Chrome bar --}}
                        <div class="flex items-center gap-2 border-b border-black/5 bg-stone-50 px-3 py-2.5">
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="size-2 rounded-full bg-red-400/80"></span>
                                <span class="size-2 rounded-full bg-amber-400/80"></span>
                                <span class="size-2 rounded-full bg-emerald-400/80"></span>
                            </div>
                            <div class="flex-1 min-w-0 flex items-center justify-center gap-1.5 rounded-lg bg-white border border-black/5 px-2.5 py-1">
                                <iconify-icon noobserver icon="hugeicons:security-check" class="text-emerald-500 text-sm shrink-0"></iconify-icon>
                                <span class="text-[11px] text-zinc-600 truncate font-medium" dir="ltr">yourbrand.sa</span>
                            </div>
                        </div>

                        {{-- Page preview --}}
                        <div class="p-4 sm:p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="size-11 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white flex items-center justify-center shadow-md shadow-primary-500/25 shrink-0">
                                    <span class="text-sm font-bold tracking-tight">YB</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-900 truncate">Your Brand</p>
                                    <p class="text-[11px] text-zinc-500 truncate">هوية متناسقة في كل زيارة</p>
                                </div>
                            </div>

                            <div class="rounded-xl bg-gradient-to-br from-primary-500/10 via-orange-50 to-stone-50 ring-1 ring-black/5 p-3 mb-4">
                                <div class="h-16 rounded-lg bg-gradient-to-l from-primary-500/30 to-primary-600/10 flex items-end p-2.5">
                                    <div class="space-y-1.5 w-full">
                                        <div class="h-2 w-2/3 rounded-full bg-white/80"></div>
                                        <div class="h-1.5 w-1/2 rounded-full bg-white/50"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 rounded-xl bg-stone-50 ring-1 ring-black/5 px-3 py-2.5 mb-4">
                                <span class="size-7 rounded-lg bg-white text-primary-600 flex items-center justify-center ring-1 ring-black/5 shrink-0">
                                    <iconify-icon noobserver icon="hugeicons:mail-01" class="text-sm"></iconify-icon>
                                </span>
                                <div class="min-w-0 leading-tight">
                                    <p class="text-[10px] text-zinc-400">بريد رسمي</p>
                                    <p class="text-xs font-medium text-zinc-800 truncate" dir="ltr">email@yourbrand.sa</p>
                                </div>
                            </div>

                            {{-- Brand colors --}}
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] text-zinc-400">ألوان الهوية</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="size-5 rounded-full bg-zinc-900 ring-2 ring-transparent"></span>
                                    <span class="size-5 rounded-full bg-amber-400 ring-2 ring-transparent"></span>
                                    <span class="size-6 rounded-full bg-primary-500 ring-2 ring-primary-500/30 ring-offset-2 ring-offset-white shadow-sm shadow-primary-500/40"></span>
                                    <span class="size-5 rounded-full bg-sky-400 ring-2 ring-transparent"></span>
                                    <span class="size-5 rounded-full bg-rose-400 ring-2 ring-transparent"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Secondary floating chip --}}
                    <div
                        class="absolute -bottom-4 end-2 sm:end-6 z-20 inline-flex items-center gap-2 rounded-2xl bg-zinc-900 text-white px-3 py-2 shadow-lg shadow-zinc-900/20 animate-[home-float_5.5s_ease-in-out_infinite]"
                        style="animation-delay: -2.8s;"
                    >
                        <span class="size-7 rounded-full bg-emerald-400/20 text-emerald-300 flex items-center justify-center">
                            <iconify-icon noobserver icon="hugeicons:security-check" class="text-sm"></iconify-icon>
                        </span>
                        <div class="leading-tight">
                            <p class="text-xs font-medium">SSL · Secure</p>
                            <p class="text-[10px] text-white/50" dir="ltr">yourbrand.sa</p>
                        </div>
                    </div>
                </div>
              </div>

            </div>
        </div>
    </section>


    

    

    <section class="bg-white border-b border-gray-100 py-16 lg:py-40 px-3 overflow-hidden">
        <div class="max-w-7xl mx-auto relative">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-8 items-center">

              <div class="lg:col-span-7 order-1">
                <p data-reveal="" class="text-4xl font-thin tracking-widest text-stone-300 flex items-center gap-2 mb-4">
                    <span class="w-6 h-px bg-stone-300"></span>
                    03
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl lg:!leading-[4rem] max-w-md lg:max-w-2xl tracking-tight">
                    صفحة
                    <b class="font-bold"> تعزز ثقة عملائك</b>
                       بك.
                </h2>

                <div class="max-w-2xl mt-8">
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        كل ما يحتاجه العميل ليطمئن ويقرر الشراء، في مكان واحد. 
                    </p>
                    <p class="text-sm text-zinc-500 leading-relaxed mt-4">
                        ابنِ الثقة قبل أن يتواصل العميل معك. اعرض التقييمات، الاعتمادات، الضمانات، وتتبع الطلبات في صفحة واحدة.
                    </p>

                    <div class="flex flex-wrap gap-1.5 lg:gap-2 mt-10 mb-10 lg:mb-2 max-w-lg lg:max-w-2xl">
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-primary-500 text-white hover:bg-primary-600 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:star" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">تقييمات العملاء</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:award-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">الاعتمادات</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:image-02" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">معرض الأعمال</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:shield-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">الضمانات</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:help-circle" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">الأسئلة الشائعة</span>
                        </div>
                        <div class="hidden lg:flex px-3 py-1.5 lg:px-4 lg:py-2 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:location-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">معلومات التواصل</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:package-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">متابعة الطلب</span>
                        </div>
                        <div class="hidden lg:flex px-3 py-1.5 lg:px-4 lg:py-2 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:invoice-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">الفواتير</span>
                        </div>
                       
                    </div>
                </div>
              </div>

              {{-- Floating trust / reviews card --}}
              <div class="lg:col-span-5 order-2 flex justify-center lg:justify-end pt-6 pb-8 lg:py-0">
                <div class="relative w-full max-w-sm mx-auto lg:mx-0 lg:-rotate-2 lg:hover:rotate-0 transition-transform duration-500">
                    <div class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-amber-300/25 via-primary-500/10 to-transparent blur-2xl pointer-events-none" aria-hidden="true"></div>

                    {{-- Verified floating chip --}}
                    <div
                        class="absolute -top-3 start-2 z-20 inline-flex items-center gap-2 rounded-full bg-emerald-600 text-white px-3 py-1.5 text-xs font-medium shadow-lg shadow-emerald-600/25 animate-[home-float_5s_ease-in-out_infinite]"
                        style="animation-delay: -1.4s;"
                    >
                        <iconify-icon noobserver icon="solar:shield-check-bold" class="text-base"></iconify-icon>
                        نشاط موثق
                    </div>

                    {{-- Main review card --}}
                    <div class="relative rounded-3xl bg-white/90 backdrop-blur-xl border border-black/5 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.18)] p-5 sm:p-6 animate-[home-float_6s_ease-in-out_infinite]">

                        {{-- Social proof hero --}}
                        <div class="rounded-2xl bg-stone-100 ring-1 ring-black/5 p-4 mb-5">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-1 text-amber-400 mb-1.5">
                                        <iconify-icon noobserver icon="solar:star-bold" class="text-sm"></iconify-icon>
                                        <iconify-icon noobserver icon="solar:star-bold" class="text-sm"></iconify-icon>
                                        <iconify-icon noobserver icon="solar:star-bold" class="text-sm"></iconify-icon>
                                        <iconify-icon noobserver icon="solar:star-bold" class="text-sm"></iconify-icon>
                                        <iconify-icon noobserver icon="solar:star-bold" class="text-sm"></iconify-icon>
                                    </div>
                                    <p class="text-4xl font-bold tracking-tight tabular-nums leading-none text-zinc-900">
                                        4.9<span class="text-lg font-medium text-zinc-400">/5</span>
                                    </p>
                                </div>
                                <div class="text-end leading-tight">
                                    <p class="text-sm font-semibold tabular-nums text-zinc-900">+1,280</p>
                                    <p class="text-[11px] text-zinc-400">تقييم</p>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-white px-3 py-2.5 ring-1 ring-black/5">
                                    <p class="text-lg font-bold tabular-nums leading-none text-zinc-900">97%</p>
                                    <p class="text-[10px] text-zinc-400 mt-1 leading-snug">يوصون بالخدمة</p>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-2.5 ring-1 ring-black/5">
                                    <p class="text-lg font-bold tabular-nums leading-none text-zinc-900">+850</p>
                                    <p class="text-[10px] text-zinc-400 mt-1 leading-snug">عميل سعيد</p>
                                </div>
                            </div>
                        </div>

                        <blockquote class="text-sm text-zinc-700 leading-relaxed">
                            «كل شيء كان واضحًا، من الدفع حتى استلام الطلب.»
                        </blockquote>
                        <div class="mt-3 flex items-center gap-2.5">
                            <span class="size-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-white text-[11px] font-bold flex items-center justify-center shrink-0">نأ</span>
                            <div class="min-w-0 leading-tight">
                                <p class="text-xs font-medium text-zinc-900">نورة العتيبي</p>
                                <p class="text-[10px] text-zinc-400">عميلة موثّقة</p>
                            </div>
                        </div>
                    </div>

                    {{-- Credentials floating stack --}}
                    <div
                        class="absolute -bottom-10 end-1 sm:end-4 z-20 flex flex-col gap-1.5 animate-[home-float_5.5s_ease-in-out_infinite]"
                        style="animation-delay: -2.6s;"
                    >
                        <div class="inline-flex items-center gap-2 rounded-xl bg-white border border-black/5 px-2 py-1 shadow-sm shadow-black/10">
                            <span class="size-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <iconify-icon noobserver icon="hugeicons:checkmark-badge-02" class="text-sm"></iconify-icon>
                            </span>
                            <span class="text-xs font-medium text-zinc-800">سجل تجاري</span>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-xl bg-white border border-black/5 px-2 py-1 shadow-sm shadow-black/10 ms-4">
                            <span class="size-7 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                                <iconify-icon noobserver icon="hugeicons:file-validation" class="text-sm"></iconify-icon>
                            </span>
                            <span class="text-xs font-medium text-zinc-800">وثيقة عمل حر</span>
                        </div>
                    </div>
                </div>
              </div>

            </div>
        </div>
    </section>


    
    

    

    <section class="bg-white/80 border-b border-gray-100 py-16 lg:py-40 px-3 overflow-hidden">
        <div class="max-w-7xl mx-auto relative">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-8 items-center">

              <div class="lg:col-span-7 order-1">
                <p data-reveal="" class="text-4xl font-thin tracking-widest text-stone-300 flex items-center gap-2 mb-4">
                    <span class="w-6 h-px bg-stone-300"></span>
                    04
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl lg:!leading-[4rem] max-w-md lg:max-w-2xl tracking-tight">
                    صفحة
                    <b class="font-bold"> تُبقي علامتك حاضرة.</b>
                </h2>

                <div class="max-w-2xl mt-8">
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        لا تجعل العلاقة مع العميل تنتهي بعد الزيارة الأولى. انشر المحتوى، وابنِ جمهورًا يعود إليك باستمرار، من مكان واحد.
                    </p>

                    <div class="flex flex-wrap gap-1.5 lg:gap-2 mt-10 mb-10 lg:mb-2 max-w-lg lg:max-w-2xl">
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-primary-500 text-white hover:bg-primary-600 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:quill-write-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">المدونة</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:mail-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">النشرة البريدية</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:video-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">الفيديوهات</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:mic-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">البودكاست</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:news" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">آخر الأخبار</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:book-open-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">المقالات والدلائل</span>
                        </div>
                        <div class="hidden lg:flex px-3 py-1.5 lg:px-4 lg:py-2 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:notification-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">تحديثات النشاط</span>
                        </div>
                    </div>
                </div>
              </div>

              {{-- Floating content feed card --}}
              <div class="lg:col-span-5 order-2 flex justify-center lg:justify-end pt-6 pb-8 lg:py-0">
                <div class="relative w-full max-w-sm mx-auto lg:mx-0 lg:rotate-2 lg:hover:rotate-0 transition-transform duration-500">
                    <div class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-amber-200/30 via-primary-500/10 to-transparent blur-2xl pointer-events-none" aria-hidden="true"></div>

                    {{-- Floating activity chip --}}
                    <div
                        class="absolute -top-3 start-2 z-20 inline-flex items-center gap-2 rounded-full bg-white border border-black/5 text-zinc-800 px-3 py-1.5 text-xs font-medium shadow-lg shadow-black/10 animate-[home-float_5s_ease-in-out_infinite]"
                        style="animation-delay: -1.3s;"
                    >
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-primary-500"></span>
                        </span>
                        محتوى جديد نُشر
                    </div>

                    {{-- Main content feed --}}
                    <div class="relative rounded-3xl bg-white/90 backdrop-blur-xl border border-black/5 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.18)] p-4 sm:p-5 animate-[home-float_6s_ease-in-out_infinite]">
                        <div class="flex items-center justify-between gap-2 mb-4 px-1">
                            <p class="text-sm font-semibold text-zinc-900">آخر التحديثات</p>
                            <span class="text-[10px] text-zinc-400">مباشر</span>
                        </div>

                        <div class="space-y-0 divide-y divide-black/5">
                            {{-- Article --}}
                            <div class="flex items-start gap-3 py-3.5 first:pt-0">
                                <span class="size-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 ring-1 ring-amber-500/10">
                                    <iconify-icon noobserver icon="hugeicons:quill-write-01" class="text-lg"></iconify-icon>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-medium text-amber-700 mb-0.5">أحدث المقالات</p>
                                    <p class="text-sm font-medium text-zinc-900 leading-snug">كيف تختار الهوية البصرية؟</p>
                                    <p class="text-[11px] text-zinc-400 mt-1">قبل يومين</p>
                                </div>
                            </div>

                            {{-- Video --}}
                            <div class="flex items-start gap-3 py-3.5">
                                <span class="size-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 ring-1 ring-rose-500/10">
                                    <iconify-icon noobserver icon="hugeicons:video-01" class="text-lg"></iconify-icon>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-medium text-rose-600 mb-0.5">فيديو جديد</p>
                                    <p class="text-sm font-medium text-zinc-900 leading-snug">كيف تزيد مبيعاتك في رمضان؟</p>
                                    <p class="text-[11px] text-zinc-400 mt-1">12K مشاهدة</p>
                                </div>
                            </div>

                            {{-- Newsletter --}}
                            <div class="flex items-start gap-3 py-3.5 last:pb-0">
                                <span class="size-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 ring-1 ring-sky-500/10">
                                    <iconify-icon noobserver icon="hugeicons:mail-01" class="text-lg"></iconify-icon>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-medium text-sky-600 mb-0.5">Newsletter</p>
                                    <p class="text-sm font-medium text-zinc-900 leading-snug">اشترك الآن</p>
                                    <p class="text-[11px] text-zinc-400 mt-1">18,000 مشترك</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Secondary floating chip --}}
                    <div
                        class="absolute -bottom-4 end-2 sm:end-6 z-20 inline-flex items-center gap-2 rounded-2xl bg-white border border-black/5 px-3 py-2 shadow-lg shadow-black/10 animate-[home-float_5.5s_ease-in-out_infinite]"
                        style="animation-delay: -2.7s;"
                    >
                        <span class="size-8 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center">
                            <iconify-icon noobserver icon="hugeicons:notification-01" class="text-base"></iconify-icon>
                        </span>
                        <div class="leading-tight">
                            <p class="text-xs font-medium text-zinc-900">تحديثات النشاط</p>
                            <p class="text-[10px] text-zinc-400">جمهورك يبقى قريبًا</p>
                        </div>
                    </div>
                </div>
              </div>

            </div>
        </div>
    </section>


        
    

    <section class="bg-white border-b border-gray-100 py-16 lg:py-40 px-3 overflow-hidden">
        <div class="max-w-7xl mx-auto relative">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-8 items-center">

              <div class="lg:col-span-7 order-1">
                <p data-reveal="" class="text-4xl font-thin tracking-widest text-stone-300 flex items-center gap-2 mb-4">
                    <span class="w-6 h-px bg-stone-300"></span>
                    05
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl lg:!leading-[4rem] max-w-md lg:max-w-2xl tracking-tight">
                    صفحة
                    <b class="font-bold"> تنمو</b>
                      مع نشاطك.
                </h2>

                <div class="max-w-2xl mt-8">
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        ابدأ بما يحتاجه نشاطك اليوم، وأضف المزيد كلما توسعت أعمالك. من متجر وحجوزات إلى محتوى وفريق عمل وتكاملات، كل شيء جاهز لينمو معك دون الحاجة إلى البدء من جديد.
                    </p>

                    <div class="flex flex-wrap gap-1.5 lg:gap-2 mt-10 mb-10 lg:mb-2 max-w-lg lg:max-w-2xl">
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-primary-500 text-white hover:bg-primary-600 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:dashboard-square-add" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">أضف أقسامًا جديدة</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:calendar-03" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">توسّع بخدمات جديدة</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:user-group" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">إدارة الفريق</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:analytics-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">إحصاءات الأداء</span>
                        </div>
                        <div class="px-3 py-1.5 lg:px-4 lg:py-2 rounded-full flex items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:flash" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">تكاملات خارجية</span>
                        </div>
                        <div class="hidden lg:flex px-3 py-1.5 lg:px-4 lg:py-2 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:plug-socket" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">ربط التطبيقات</span>
                        </div>
                        <div class="hidden lg:flex px-3 py-1.5 lg:px-4 lg:py-2 rounded-full items-center gap-1.5 lg:gap-2 bg-stone-200 hover:bg-stone-300/80 transition-colors interactive cursor-hover">
                            <iconify-icon noobserver icon="hugeicons:rocket-01" class="text-sm lg:text-xl" stroke-width="1.5"></iconify-icon>
                            <span class="text-sm">تحديثات مستمرة</span>
                        </div>
                    </div>
                </div>
              </div>

              {{-- Floating growth / modules card --}}
              <div class="lg:col-span-5 order-2 flex justify-center lg:justify-end pt-6 pb-8 lg:py-0">
                <div class="relative w-full max-w-sm mx-auto lg:mx-0 lg:-rotate-2 lg:hover:rotate-0 transition-transform duration-500">
                    <div class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-primary-500/15 via-lime-200/25 to-transparent blur-2xl pointer-events-none" aria-hidden="true"></div>

                    {{-- Floating chip --}}
                    <div
                        class="absolute -top-3 start-2 z-20 inline-flex items-center gap-2 rounded-full bg-white border border-black/5 text-zinc-800 px-3 py-1.5 text-xs font-medium shadow-lg shadow-black/10 animate-[home-float_5s_ease-in-out_infinite]"
                        style="animation-delay: -1.2s;"
                    >
                        <span class="size-6 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center">
                            <iconify-icon noobserver icon="hugeicons:dashboard-square-add" class="text-sm"></iconify-icon>
                        </span>
                        قسم جديد مفعّل
                    </div>

                    {{-- Main growth card --}}
                    <div class="relative rounded-3xl bg-white/90 backdrop-blur-xl border border-black/5 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.18)] p-5 sm:p-6 animate-[home-float_6s_ease-in-out_infinite]">
                        <div class="flex items-start justify-between gap-3 mb-5">
                            <div>
                                <p class="text-[11px] tracking-widest text-zinc-400">نمو الصفحة</p>
                                <h3 class="text-lg font-semibold text-zinc-900 mt-0.5">صفحتك تنمو معك</h3>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-1 text-xs font-medium ring-1 ring-emerald-600/10">
                                <iconify-icon noobserver icon="hugeicons:arrow-up-02" class="text-sm"></iconify-icon>
                                +3 هذا الشهر
                            </span>
                        </div>

                        {{-- Growth progress --}}
                        <div class="rounded-2xl bg-stone-100 ring-1 ring-black/5 p-3.5 mb-4">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="text-zinc-500">الأقسام المفعّلة</span>
                                <span class="font-semibold text-zinc-800 tabular-nums">6 / 12</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-white overflow-hidden ring-1 ring-black/5">
                                <div class="h-full w-1/2 rounded-full bg-gradient-to-l from-primary-500 to-primary-400"></div>
                            </div>
                        </div>

                        {{-- Active modules --}}
                        <div class="space-y-2.5">
                            <div class="flex items-center gap-3 rounded-2xl bg-stone-50 ring-1 ring-black/5 px-3 py-2.5">
                                <span class="size-9 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                                    <iconify-icon noobserver icon="hugeicons:shopping-cart-01" class="text-base"></iconify-icon>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-900">متجر إلكتروني</p>
                                    <p class="text-[10px] text-zinc-400">نشط</p>
                                </div>
                                <span class="size-2 rounded-full bg-emerald-500 shrink-0"></span>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl bg-stone-50 ring-1 ring-black/5 px-3 py-2.5">
                                <span class="size-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                                    <iconify-icon noobserver icon="hugeicons:calendar-03" class="text-base"></iconify-icon>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-900">حجز المواعيد</p>
                                    <p class="text-[10px] text-zinc-400">نشط</p>
                                </div>
                                <span class="size-2 rounded-full bg-emerald-500 shrink-0"></span>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl border border-dashed border-black/10 px-3 py-2.5">
                                <span class="size-9 rounded-xl bg-stone-100 text-zinc-400 flex items-center justify-center shrink-0">
                                    <iconify-icon noobserver icon="hugeicons:add-01" class="text-base"></iconify-icon>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-600">أضف قسمًا جديدًا</p>
                                    <p class="text-[10px] text-zinc-400">مدونة · فريق · تكاملات</p>
                                </div>
                            </div>
                        </div>

                        {{-- Team row --}}
                        <div class="mt-4 flex items-center justify-between gap-2 pt-1">
                            <div class="flex items-center gap-2">
                                <div class="flex -space-x-2 space-x-reverse">
                                    <span class="size-7 rounded-full bg-primary-500 text-white text-[10px] font-bold ring-2 ring-white flex items-center justify-center">أ</span>
                                    <span class="size-7 rounded-full bg-amber-400 text-white text-[10px] font-bold ring-2 ring-white flex items-center justify-center">س</span>
                                    <span class="size-7 rounded-full bg-sky-500 text-white text-[10px] font-bold ring-2 ring-white flex items-center justify-center">م</span>
                                </div>
                                <span class="text-[11px] text-zinc-400">3 أعضاء</span>
                            </div>
                            <span class="text-[11px] font-medium text-primary-600">إدارة الفريق</span>
                        </div>
                    </div>

                    {{-- Secondary floating chip --}}
                    <div
                        class="absolute -bottom-4 end-2 sm:end-6 z-20 inline-flex items-center gap-2 rounded-2xl bg-white border border-black/5 px-3 py-2 shadow-lg shadow-black/10 animate-[home-float_5.5s_ease-in-out_infinite]"
                        style="animation-delay: -2.8s;"
                    >
                        <span class="size-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <iconify-icon noobserver icon="hugeicons:analytics-01" class="text-base"></iconify-icon>
                        </span>
                        <div class="leading-tight">
                            <p class="text-xs font-medium text-zinc-900">+42% نمو</p>
                            <p class="text-[10px] text-zinc-400">خلال 30 يومًا</p>
                        </div>
                    </div>
                </div>
              </div>

            </div>
        </div>
    </section>


    


    
    <section id="pricing" class="relative px-4 sm:px-8 py-40 overflow-hidden bg-white border-t-2 border-gray-100">
        <div data-parallax="0.3" class="absolute -top-20 -left-32 w-[28rem] h-[28rem] rounded-full bg-primary-100/70 blur-3xl pointer-events-none" style="translate: none; rotate: none; scale: none; transform: translate3d(0px, -81.5715px, 0px);"></div>
        <div class="max-w-7xl mx-auto relative">
          <div class="flex flex-col  gap-6 mb-14">
            <div class="">
              <p data-reveal="" class="text-xs tracking-widest text-orange-500 flex items-center gap-2 mb-4" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                <span class="w-6 h-px bg-orange-400"></span>
                أسعار واضحة، دون مفاجآت.
              </p>
              <h2 data-reveal="" class="text-4xl sm:text-5xl  max-w-sm tracking-tight leading-tight" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                 <b class="font-bold"> ابدأ مجانًا، </b>
                 وطوّر باقتك لاحقًا.
              </h2>
            </div>
            <p data-reveal="" class="text-lg text-zinc-500 max-w-sm leading-relaxed" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                كل ما تحتاجه للانطلاق، مع إمكانية الترقية في أي وقت.
            </p>
          </div>
          <div class="grid md:grid-cols-3 gap-6 items-stretch">
            <div data-reveal="" class="liquid-border border relative rounded-3xl bg-white/70 backdrop-blur-xl p-8 flex flex-col   transition-transform duration-300 " style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
              <div class="flex items-center gap-3 mb-8">
                <span class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="feather" aria-hidden="true" class="lucide lucide-feather w-5 h-5 text-primary-700"><path d="M12.67 19a2 2 0 0 0 1.416-.588l6.154-6.172a6 6 0 0 0-8.49-8.49L5.586 9.914A2 2 0 0 0 5 11.328V18a1 1 0 0 0 1 1z"></path><path d="M16 8 2 22"></path><path d="M17.5 15H9"></path></svg>
                </span>
                <p class="font-medium">بداية</p>
              </div>
              <p class="text-5xl font-medium tracking-tight">
                مجاناً
                {{-- <span class="text-base font-normal text-zinc-500">/mo</span> --}}
              </p>
              <p class="text-sm text-zinc-500 mt-3 leading-relaxed">
                 ابدأ مجانًا، ثم طوّر باقتك مع نمو نشاطك.
                
              </p>
              <ul class="space-y-3 mt-8 mb-8 text-sm text-zinc-600 flex-1">
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>صفحة احترافية</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>رابط ثابت مجاني</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>كيو آر كود QR Code</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>استقبل الطلبات والمشتريات لمنتجاتك وخدماتك</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>خيارات شحن مخصصة</span>
                </li>
                <li class="flex items-start gap-2 text-zinc-400/70">
                  <iconify-icon noobserver icon="hugeicons:cancel-01" class="text-base text-zinc-300 shrink-0 mt-0.5"></iconify-icon>
                  <span>دومين مخصص</span>
                </li>
                <li class="flex items-start gap-2 text-zinc-400/70">
                  <iconify-icon noobserver icon="hugeicons:cancel-01" class="text-base text-zinc-300 shrink-0 mt-0.5"></iconify-icon>
                  <span>ايميل رسمي</span>
                </li>
                <li class="flex items-start gap-2 text-zinc-400/70">
                  <iconify-icon noobserver icon="hugeicons:cancel-01" class="text-base text-zinc-300 shrink-0 mt-0.5"></iconify-icon>
                  <span>تفعيل بوابات الدفع الرقمية</span>
                </li>
              </ul>
              <a href="{{ route('auth.register-login') }}" wire:navigate class="group mt-auto inline-flex w-full items-center justify-center gap-2 rounded-full bg-zinc-900 px-6 py-3.5 text-base font-medium text-white transition-all duration-300 hover:bg-primary-700">
                ابدأ الآن
                <iconify-icon noobserver icon="hugeicons:arrow-left-02" class="text-lg transition-transform group-hover:-translate-x-0.5"></iconify-icon>
              </a>
            </div>
            <div data-reveal="" class="liquid-border liquid-border-dark relative rounded-3xl bg-zinc-900 text-white p-8 flex flex-col   transition-transform duration-300 overflow-hidden ">
              <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-primary-500/30 blur-3xl pointer-events-none"></div>
              <div class="relative flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                  <span class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="sparkles" aria-hidden="true" class="lucide lucide-sparkles w-5 h-5 text-white"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path><path d="M20 2v4"></path><path d="M22 4h-4"></path><circle cx="4" cy="20" r="2"></circle></svg>
                  </span>
                  <p class="font-medium">انطلاق</p>
                </div>
                {{-- <span class="text-[10px] tracking-widest bg-primary-500 text-zinc-900 font-semibold px-3 py-1 rounded-full">
                  POPULAR
                </span> --}}
              </div>
              <p class="relative text-5xl font-medium tracking-tight">
                 99  <span class="icon-saudi_riyal"></span>
                <span class="text-base font-normal text-zinc-400">/شهرياً</span>
              </p>
              <p class="relative text-sm text-zinc-400 mt-3 leading-relaxed">
                كل ما تحتاجه لإطلاق صفحة أعمال احترافية متكاملة.
                
              </p>
              <ul class="relative space-y-3 mt-8 mb-8 text-sm text-zinc-300 flex-1">
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-400 shrink-0 mt-0.5"></iconify-icon>
                  <span>كل مزايا باقة بداية، بالإضافة إلى</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-400 shrink-0 mt-0.5"></iconify-icon>
                  <span>دومين مخصص</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-400 shrink-0 mt-0.5"></iconify-icon>
                  <span>ايميل رسمي عدد 2 ايميلات رسمية</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-400 shrink-0 mt-0.5"></iconify-icon>
                  <span>استقبال جميع بوابات الدفع + تابي + تمارا</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-400 shrink-0 mt-0.5"></iconify-icon>
                  <span>ربط 240+ بوابة شحن لمنتجاتك الملموسة</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-400 shrink-0 mt-0.5"></iconify-icon>
                  <span>إحصاءات متقدمة</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-400 shrink-0 mt-0.5"></iconify-icon>
                  <span>تكاملات أساسية Integrations</span>
                </li>
                <li class="flex items-start gap-2 text-zinc-500">
                  <iconify-icon noobserver icon="hugeicons:cancel-01" class="text-base text-zinc-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>إدارة الفريق والصلاحيات</span>
                </li>
                <li class="flex items-start gap-2 text-zinc-500">
                  <iconify-icon noobserver icon="hugeicons:cancel-01" class="text-base text-zinc-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>إزالة شعار إقليم</span>
                </li>
              </ul>
              <a href="{{ route('auth.register-login') }}" wire:navigate class="group relative mt-auto inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary-600 px-6 py-3.5 text-base font-semibold text-white shadow-lg shadow-primary-600/25 transition-all duration-300 hover:bg-primary-500">
                اختر انطلاق
                <iconify-icon noobserver icon="hugeicons:arrow-left-02" class="text-lg transition-transform group-hover:-translate-x-0.5"></iconify-icon>
              </a>
            </div>

            <div data-reveal="" class="liquid-border border relative rounded-3xl bg-white/70 backdrop-blur-xl p-8 flex flex-col  transition-transform duration-300  " style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
              <div class="flex items-center gap-3 mb-8">
                <span class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="gem" aria-hidden="true" class="lucide lucide-gem w-5 h-5 text-primary-700"><path d="M10.5 3 8 9l4 13 4-13-2.5-6"></path><path d="M17 3a2 2 0 0 1 1.6.8l3 4a2 2 0 0 1 .013 2.382l-7.99 10.986a2 2 0 0 1-3.247 0l-7.99-10.986A2 2 0 0 1 2.4 7.8l2.998-3.997A2 2 0 0 1 7 3z"></path><path d="M2 9h20"></path></svg>
                </span>
                <p class="font-medium">نمو</p>
              </div>
              <p class="text-5xl font-medium tracking-tight">
                299 <span class="icon-saudi_riyal"></span>
                <span class="text-base font-normal text-zinc-500">/شهريا</span>
              </p>
              <p class="text-sm text-zinc-500 mt-3 leading-relaxed">
                مزايا متقدمة لإدارة نشاطك وبناء حضور رقمي يدوم.
              </p>
              <ul class="space-y-3 mt-8 mb-8 text-sm text-zinc-600 flex-1">
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>كل مزايا باقة انطلاق، بالإضافة إلى</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>إدارة فريق العمل حتى 5 أعضاء</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>ايميل رسمي عدد 25</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>قوالب مخصصة</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>إزالة شعار إقليم</span>
                </li>
                <li class="flex items-start gap-2">
                  <iconify-icon noobserver icon="hugeicons:tick-02" class="text-base text-primary-600 shrink-0 mt-0.5"></iconify-icon>
                  <span>جميع التكاملات Integrations</span>
                </li>
              </ul>
              <a href="{{ route('auth.register-login') }}" wire:navigate class="group mt-auto inline-flex w-full items-center justify-center gap-2 rounded-full bg-zinc-900 px-6 py-3.5 text-base font-medium text-white transition-all duration-300 hover:bg-primary-700">
                اختر نمو
                <iconify-icon noobserver icon="hugeicons:arrow-left-02" class="text-lg transition-transform group-hover:-translate-x-0.5"></iconify-icon>
              </a>
            </div>
          </div>
        </div>
      </section>



      <section class="py-24  hidden">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="inline-flex items-center gap-2 text-xs font-medium bg-black/5 text-[#0E0E0E] px-3 py-1.5 rounded-full mb-6">
                <iconify-icon noobserver icon="solar:eye-closed-linear" class="text-[#6B7280]"></iconify-icon>
                  أسئلة قد تدور في ذهنك.
            </div>
            
            <h2 class="text-3xl md:text-4xl font-medium tracking-tight text-gray-900 mb-12 text-center">إجابات على أكثر ما يهم أصحاب الأعمال.</h2>
            
            <div class="space-y-4" x-data="{ active: 1 }" x-cloak>
                <!-- FAQ Item 1 -->
                <div
                    class="border border-gray-200 rounded-2xl overflow-hidden transition-colors"
                    :class="active === 1 ? 'bg-white' : 'bg-gray-50/50'"
                    role="region"
                >
                    <h3>
                        <button
                            type="button"
                            @click="active = active === 1 ? null : 1"
                            :aria-expanded="active === 1"
                            class="w-full px-6 py-5 text-start flex justify-between items-center gap-4 focus:outline-none"
                        >
                            <span class="text-lg font-medium text-gray-900">هل المنصة مناسبة لنشاطي؟</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-plus w-5 h-5 shrink-0 transition-transform duration-300" :class="active === 1 ? 'rotate-45 text-gray-900' : 'text-gray-500'"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        </button>
                    </h3>
                    <div x-show="active === 1" x-collapse>
                        <div class="px-6 pb-5 text-base text-gray-600 font-light leading-relaxed">
                            إذا كنت تبيع منتجات، تقدم خدمات، تستقبل حجوزات، تؤجر وحدات، أو ترغب في بناء حضور احترافي لعلامتك التجارية، فالمنصة مصممة لتناسب مختلف الأنشطة.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div
                    class="border border-gray-200 rounded-2xl overflow-hidden transition-colors"
                    :class="active === 2 ? 'bg-white' : 'bg-gray-50/50'"
                    role="region"
                >
                    <h3>
                        <button
                            type="button"
                            @click="active = active === 2 ? null : 2"
                            :aria-expanded="active === 2"
                            class="w-full px-6 py-5 text-start flex justify-between items-center gap-4 focus:outline-none"
                        >
                            <span class="text-lg font-medium text-gray-900">هل أحتاج إلى خبرة تقنية؟</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-plus w-5 h-5 shrink-0 transition-transform duration-300" :class="active === 2 ? 'rotate-45 text-gray-900' : 'text-gray-500'"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        </button>
                    </h3>
                    <div x-show="active === 2" x-collapse>
                        <div class="px-6 pb-5 text-base text-gray-600 font-light leading-relaxed">
                            لا، يمكنك إنشاء صفحتك وتخصيصها وإدارتها بسهولة، دون الحاجة إلى أي خبرة برمجية أو تصميمية.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div
                    class="border border-gray-200 rounded-2xl overflow-hidden transition-colors"
                    :class="active === 3 ? 'bg-white' : 'bg-gray-50/50'"
                    role="region"
                >
                    <h3>
                        <button
                            type="button"
                            @click="active = active === 3 ? null : 3"
                            :aria-expanded="active === 3"
                            class="w-full px-6 py-5 text-start flex justify-between items-center gap-4 focus:outline-none"
                        >
                            <span class="text-lg font-medium text-gray-900">هل أستطيع استخدام دومين خاص وهوية نشاطي؟</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-plus w-5 h-5 shrink-0 transition-transform duration-300" :class="active === 3 ? 'rotate-45 text-gray-900' : 'text-gray-500'"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        </button>
                    </h3>
                    <div x-show="active === 3" x-collapse>
                        <div class="px-6 pb-5 text-base text-gray-600 font-light leading-relaxed">
                            نعم، يمكنك ربط دومينك الخاص، وتخصيص الألوان والشعار والخطوط لتظهر صفحتك بهوية علامتك التجارية.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div
                    class="border border-gray-200 rounded-2xl overflow-hidden transition-colors"
                    :class="active === 4 ? 'bg-white' : 'bg-gray-50/50'"
                    role="region"
                >
                    <h3>
                        <button
                            type="button"
                            @click="active = active === 4 ? null : 4"
                            :aria-expanded="active === 4"
                            class="w-full px-6 py-5 text-start flex justify-between items-center gap-4 focus:outline-none"
                        >
                            <span class="text-lg font-medium text-gray-900">هل أستطيع استقبال الطلبات والمدفوعات من الصفحة مباشرة؟</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-plus w-5 h-5 shrink-0 transition-transform duration-300" :class="active === 4 ? 'rotate-45 text-gray-900' : 'text-gray-500'"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        </button>
                    </h3>
                    <div x-show="active === 4" x-collapse>
                        <div class="px-6 pb-5 text-base text-gray-600 font-light leading-relaxed">
                            نعم، يمكنك استقبال الطلبات والحجوزات والمدفوعات مباشرة من صفحتك، مع ربط وسائل الدفع والشحن المدعومة، دون الحاجة إلى منصات إضافية.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div
                    class="border border-gray-200 rounded-2xl overflow-hidden transition-colors"
                    :class="active === 5 ? 'bg-white' : 'bg-gray-50/50'"
                    role="region"
                >
                    <h3>
                        <button
                            type="button"
                            @click="active = active === 5 ? null : 5"
                            :aria-expanded="active === 5"
                            class="w-full px-6 py-5 text-start flex justify-between items-center gap-4 focus:outline-none"
                        >
                            <span class="text-lg font-medium text-gray-900">هل سأحتاج إلى إعادة بناء صفحتي عندما يكبر نشاطي؟</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-plus w-5 h-5 shrink-0 transition-transform duration-300" :class="active === 5 ? 'rotate-45 text-gray-900' : 'text-gray-500'"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        </button>
                    </h3>
                    <div x-show="active === 5" x-collapse>
                        <div class="px-6 pb-5 text-base text-gray-600 font-light leading-relaxed">
                            لا، ابدأ بما يحتاجه نشاطك اليوم، ثم أضف أقسامًا جديدة أو رقِّ باقتك في أي وقت، مع الاحتفاظ بصفحتك وجميع بياناتك.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div
                    class="border border-gray-200 rounded-2xl overflow-hidden transition-colors"
                    :class="active === 6 ? 'bg-white' : 'bg-gray-50/50'"
                    role="region"
                >
                    <h3>
                        <button
                            type="button"
                            @click="active = active === 6 ? null : 6"
                            :aria-expanded="active === 6"
                            class="w-full px-6 py-5 text-start flex justify-between items-center gap-4 focus:outline-none"
                        >
                            <span class="text-lg font-medium text-gray-900">هل بيانات العملاء والطلبات ملك لي؟</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-plus w-5 h-5 shrink-0 transition-transform duration-300" :class="active === 6 ? 'rotate-45 text-gray-900' : 'text-gray-500'"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        </button>
                    </h3>
                    <div x-show="active === 6" x-collapse>
                        <div class="px-6 pb-5 text-base text-gray-600 font-light leading-relaxed">
                            نعم، جميع بيانات العملاء والطلبات والمشتريات محفوظة داخل حسابك، ويمكنك إدارتها والرجوع إليها في أي وقت.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
 


    <section id="faq" class="bg-white/80 px-6 py-24 lg:py-40">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-16">
          <div class="lg:col-span-5 scroll-fade" style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px); opacity: 1;">
            
            {{-- <div class="inline-flex items-center gap-2 text-xs font-medium bg-black/5 text-[#0E0E0E] px-3 py-1.5 rounded-full mb-6">
                <iconify-icon noobserver icon="solar:eye-closed-linear" class="text-[#6B7280]"></iconify-icon>
                  أسئلة قد تدور في ذهنك.
            </div> --}}
            <h2 class="text-4xl md:text-5xl tracking-tight max-w-md font-normal lg:!leading-[4rem] mb-4 text-balance">
                كل ما تحتاج إلى  
                <b>معرفته</b>
                قبل البدء.
            </h2>
            <p class="text-sm text-[#2C2825]/70 max-w-md ">
                إجابات واضحة ومختصرة على أكثر الأسئلة التي يطرحها أصحاب الأعمال قبل إنشاء صفحاتهم.
            </p>
          </div>

          <div class="lg:col-span-7 flex flex-col justify-center">
            <div class="accordion-group border-t border-[#2C2825]/10" x-data="{ active: 1 }" x-cloak>
              <!-- Q1 -->
              <div class="accordion-item border-b border-[#2C2825]/10 py-5" role="region">
                <button
                  type="button"
                  @click="active = active === 1 ? null : 1"
                  :aria-expanded="active === 1"
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-stone-800 transition-colors"
                  :class="active === 1 ? 'text-stone-800' : ''"
                >
                  <span class="text-sm md:text-base font-bold flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">01.</span>
                    هل المنصة مناسبة لنشاطي؟
                  </span>
                  <iconify-icon noobserver icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 1 ? 'rotate-180' : ''"></iconify-icon>
                </button>
                <div x-show="active === 1" x-collapse>
                  <p class="text-sm text-[#2C2825]/70 pt-4 pl-8 pb-2 leading-relaxed">
                    إذا كنت تبيع منتجات، تقدم خدمات، تستقبل حجوزات، تؤجر وحدات، أو ترغب في بناء حضور احترافي لعلامتك التجارية، فالمنصة مصممة لتناسب مختلف الأنشطة.
                  </p>
                </div>
              </div>
              <!-- Q2 -->
              <div class="accordion-item border-b border-[#2C2825]/10 py-5" role="region">
                <button
                  type="button"
                  @click="active = active === 2 ? null : 2"
                  :aria-expanded="active === 2"
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-stone-800 transition-colors"
                  :class="active === 2 ? 'text-stone-800' : ''"
                >
                  <span class="text-sm md:text-base font-bold flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">02.</span>
                    هل أحتاج إلى خبرة تقنية؟
                  </span>
                  <iconify-icon noobserver icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 2 ? 'rotate-180' : ''"></iconify-icon>
                </button>
                <div x-show="active === 2" x-collapse>
                  <p class="text-sm text-[#2C2825]/70 pt-4 pl-8 pb-2 leading-relaxed">
                    لا، يمكنك إنشاء صفحتك وتخصيصها وإدارتها بسهولة، دون الحاجة إلى أي خبرة برمجية أو تصميمية.
                  </p>
                </div>
              </div>
              <!-- Q3 -->
              <div class="accordion-item border-b border-[#2C2825]/10 py-5" role="region">
                <button
                  type="button"
                  @click="active = active === 3 ? null : 3"
                  :aria-expanded="active === 3"
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-stone-800 transition-colors"
                  :class="active === 3 ? 'text-stone-800' : ''"
                >
                  <span class="text-sm md:text-base font-bold flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">03.</span>
                    هل أستطيع استخدام دومين خاص وهوية نشاطي؟
                  </span>
                  <iconify-icon noobserver icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 3 ? 'rotate-180' : ''"></iconify-icon>
                </button>
                <div x-show="active === 3" x-collapse>
                  <p class="text-sm text-[#2C2825]/70 pt-4 pl-8 pb-2 leading-relaxed">
                    نعم، يمكنك ربط دومينك الخاص، وتخصيص الألوان والشعار والخطوط لتظهر صفحتك بهوية علامتك التجارية.
                  </p>
                </div>
              </div>
              <!-- Q4 -->
              <div class="accordion-item border-b border-[#2C2825]/10 py-5" role="region">
                <button
                  type="button"
                  @click="active = active === 4 ? null : 4"
                  :aria-expanded="active === 4"
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-stone-800 transition-colors"
                  :class="active === 4 ? 'text-stone-800' : ''"
                >
                  <span class="text-sm md:text-base font-bold flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">04.</span>
                    هل أستطيع استقبال الطلبات والمدفوعات من الصفحة مباشرة؟
                  </span>
                  <iconify-icon noobserver icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 4 ? 'rotate-180' : ''"></iconify-icon>
                </button>
                <div x-show="active === 4" x-collapse>
                  <p class="text-sm text-[#2C2825]/70 pt-4 pl-8 pb-2 leading-relaxed">
                    نعم، يمكنك استقبال الطلبات والحجوزات والمدفوعات مباشرة من صفحتك، مع ربط وسائل الدفع والشحن المدعومة، دون الحاجة إلى منصات إضافية.
                  </p>
                </div>
              </div>
              <!-- Q5 -->
              <div class="accordion-item border-b border-[#2C2825]/10 py-5" role="region">
                <button
                  type="button"
                  @click="active = active === 5 ? null : 5"
                  :aria-expanded="active === 5"
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-stone-800 transition-colors"
                  :class="active === 5 ? 'text-stone-800' : ''"
                >
                  <span class="text-sm md:text-base font-bold flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">05.</span>
                    هل سأحتاج إلى إعادة بناء صفحتي عندما يكبر نشاطي؟
                  </span>
                  <iconify-icon noobserver icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 5 ? 'rotate-180' : ''"></iconify-icon>
                </button>
                <div x-show="active === 5" x-collapse>
                  <p class="text-sm text-[#2C2825]/70 pt-4 pl-8 pb-2 leading-relaxed">
                    لا، ابدأ بما يحتاجه نشاطك اليوم، ثم أضف أقسامًا جديدة أو رقِّ باقتك في أي وقت، مع الاحتفاظ بصفحتك وجميع بياناتك.
                  </p>
                </div>
              </div>
              <!-- Q6 -->
              <div class="accordion-item border-b border-[#2C2825]/10 py-5" role="region">
                <button
                  type="button"
                  @click="active = active === 6 ? null : 6"
                  :aria-expanded="active === 6"
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-stone-800 transition-colors"
                  :class="active === 6 ? 'text-stone-800' : ''"
                >
                  <span class="text-sm md:text-base font-bold flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">06.</span>
                    هل بيانات العملاء والطلبات ملك لي؟
                  </span>
                  <iconify-icon noobserver icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 6 ? 'rotate-180' : ''"></iconify-icon>
                </button>
                <div x-show="active === 6" x-collapse>
                  <p class="text-sm text-[#2C2825]/70 pt-4 pl-8 pb-2 leading-relaxed">
                    نعم، جميع بيانات العملاء والطلبات والمشتريات محفوظة داخل حسابك، ويمكنك إدارتها والرجوع إليها في أي وقت.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>




      {{-- Pre-footer CTA --}}
      <section class="px-6 py-20 lg:py-32 bg-stone-200/80 border-y border-stone-300/40">
        <div class="max-w-3xl mx-auto text-center">
            <p class="text-sm text-stone-600 flex items-center justify-center gap-2 mb-6">
                <span class="w-6 h-px bg-stone-500"></span>
                استثمر في حضور رقمي يبقى معك، لا في حملة إعلانية تنتهي غداً
            </p>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl tracking-tight leading-[1.25] lg:!leading-[3.75rem] text-zinc-900 text-balance">
                ماذا لو كانت صفحتك
                <b class="font-bold">أفضل موظف مبيعات عندك؟</b>
            </h2>

            <div class="mt-6 space-y-3 text-base sm:text-lg text-zinc-500 leading-relaxed max-w-2xl mx-auto">
                <p>
                    كل ما يحتاجه عملاؤك في مكان واحد، وكل ما تحتاجه لإدارة حضورك الرقمي في منصة واحدة.
                </p>
                <p>
                    صفحة تبيع، وتحجز، وتعزز الثقة، وتنمو معك... من أول يوم.
                </p>
            </div>

            <div class="mt-10">
                <a href="{{ route('auth.register-login') }}" wire:navigate class="group inline-flex items-center justify-center gap-px">
                    <span class="bg-zinc-900 text-white text-base sm:text-lg font-medium inline-flex items-center justify-center px-7 py-3.5 rounded-full transition-all duration-300 group-hover:bg-primary-700">
                        أنشئ صفحتي الآن، مجاناً
                    </span>
                    <span class="size-12 rounded-full bg-zinc-900 text-white flex items-center justify-center transition-all duration-300 rotate-[-135deg] group-hover:bg-primary-700 group-hover:rotate-[-130deg]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M7 7h10v10"></path><path d="M7 17 17 7"></path></svg>
                    </span>
                </a>
            </div>
        </div>
      </section>




      <footer class="w-full px-6 lg:px-10 pt-16 pb-8 bg-white xborder-t border-[#EBEBEB]">
        <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-8 mb-12">
            <div class="flex flex-col md:flex-row items-center gap-8 lg:gap-12 w-full">
                <a href="{{ route('home') }}" wire:navigate class="text-[#0D0C22] shrink-0 flex items-center gap-2 font-brand font-medium text-xl tracking-tight" aria-label="{{ config('app.name') }}">
                    <img class="w-auto h-7" src="{{ asset('assets/images/logo.webp') }}" alt="{{ config('app.name') }}" />
                </a>
                <ul class="flex flex-wrap justify-center md:justify-start gap-6 lg:gap-8 text-base font-medium text-[#0D0C22]">
                    <li><a href="#pricing" class="hover:text-gray-600 transition-colors">الباقات والأسعار</a></li>
                    <li><a href="#faq" class="hover:text-gray-600 transition-colors">الأسئلة الشائعة</a></li>
                    <li><a href="{{ route('auth.register-login') }}" wire:navigate class="hover:text-gray-600 transition-colors">أنشئ صفحتك</a></li>
                    <li><a href="{{ route('auth.register-login') }}" wire:navigate class="hover:text-gray-600 transition-colors">تسجيل الدخول</a></li>
                </ul>
            </div>
            <div class="flex items-center gap-4 shrink-0 text-[#0D0C22]">
                <a href="https://x.com/broshurcom" target="_blank" rel="noopener noreferrer" class="hover:text-gray-600 transition-colors" aria-label="X"><iconify-icon noobserver icon="solar:hashtag-linear" class="w-5 h-5"></iconify-icon></a>
                <a href="{{ route('home') }}" wire:navigate class="hover:text-gray-600 transition-colors" aria-label="{{ config('app.name') }}"><iconify-icon noobserver icon="solar:global-linear" class="w-5 h-5"></iconify-icon></a>
                <a href="https://www.instagram.com/broshurcom" target="_blank" rel="noopener noreferrer" class="hover:text-gray-600 transition-colors" aria-label="إنستغرام"><iconify-icon noobserver icon="solar:camera-linear" class="w-5 h-5"></iconify-icon></a>
                <a href="{{ route('auth.register-login') }}" wire:navigate class="hover:text-gray-600 transition-colors" aria-label="ابدأ الآن"><iconify-icon noobserver icon="solar:play-circle-linear" class="w-5 h-5"></iconify-icon></a>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm font-normal text-[#6E6D7A]">
            
            <div class="flex items-center gap-4 flex-wrap justify-center">
                <a href="{{ route('terms') }}" wire:navigate class="hover:text-[#0D0C22]">الشروط والأحكام</a>
                <a href="{{ route('privacy') }}" wire:navigate class="hover:text-[#0D0C22]">سياسة الخصوصية</a>
                <a href="{{ route('contact') }}" wire:navigate class="hover:text-[#0D0C22]">اتصل بنا</a>
            </div>
            <div class="flex items-center gap-4">
                <span>
                    <span class="hidden lg:inline">{{ config('app.name') }}</span>
                    © {{ date('Y') }}</span>
            </div>
        </div>
    </footer>

</div>

<?php 
 
new 
#[\Livewire\Attributes\Title('أنشئ صفحة لأعمالك، تبيع عنّك بدقائق!')]
class extends \Livewire\Component {
     
}; ?>
