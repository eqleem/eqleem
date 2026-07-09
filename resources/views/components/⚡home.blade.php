<div class="min-h-screen relative  bg-stone-200 text-[#111111] antialiased overflow-x-hidden selection:bg-[#EAEAEA] selection:text-[#111111]">
 
    <nav class="fixedX top-0 w-full z-50    transition-all duration-300">
        <div class="max-w-7xl mx-auto px-3 md:px-2 xl:px-0 h-[4.5rem] md:h-[5rem] flex items-center justify-between">
            <a href="{{ route('home') }}" wire:navigate class="font-display text-xl font-medium tracking-tighter text-[#111111] flex items-center gap-2">
                <img class="w-auto h-7 md:h-8" src="{{ asset('assets/images/logo.webp') }}" alt="" />
            </a>
          
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('admin.home') }}" wire:navigate class="inline-flex items-center justify-center h-10 px-5 shrink-0 rounded-full bg-[#111111] text-white text-sm font-normal hover:bg-[#333333] transition-colors">
                        لوحة التحكم
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-right size-4 ms-3 group-hover:-translate-x-1 transition-transform -rotate-180"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>
                @else
                    <a href="{{ route('auth.register') }}" wire:navigate class="inline-flex items-center justify-center h-10 px-5 shrink-0 rounded-full bg-[#111111] text-white text-sm font-normal hover:bg-[#333333] transition-colors">
                        <span class="inline md:hidden"> تسجيل </span>
                        <span class="hidden md:inline">أنش صفحتي الآن </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-right size-4 ms-3 group-hover:-translate-x-1 transition-transform -rotate-180"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('auth.login') }}" wire:navigate class="inline-flex items-center justify-center p-2 px-4 rounded-full border border-black/10 text-[#111111] text-sm font-normal hover:bg-black/5 transition-colors w-full sm:w-auto gap-2 group">
                        دخول 
                        <iconify-icon icon="solar:arrow-left-linear" stroke-width="1.5" class=" transition-transform hidden md:block"></iconify-icon>
                    </a>
                @endauth
  
            </div>
        </div>
    </nav>


    <section class="relative  w-full  px-6 flex items-center justify-start min-h-[calc(100vh-15.7rem)] ">
        <div class="glow-bg top-[10%] left-[50%] -translate-x-[50%]"></div>
        
        <div class="max-w-7xl mx-auto w-full gap-12 lg:gap-8">
            <div class="flex flex-col gap-8 reveal-up active">
                <p data-reveal="" class="text-xs tracking-widest text-stone-500 flex items-center gap-2">
                    <span class="w-6 h-px bg-stone-500"></span>
                     مصممة لأصحاب الأعمال في السعودية
                     <span class="text-2xl">🇸🇦</span>
                  </p>
                <h1 class="-mt-3 text-[3.5rem] md:text-[4rem] 2xl:text-[4.5rem] leading-[1.2]   tracking-tighter text-[#111111]">
                    <span class="hero-line block" style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px);">
                        ابنِ
                        <span class="relative inline-block text-[#C94309]">
                            صفحة
                          <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 120 12" fill="none">
                            <path id="underline" d="M3 9 C 30 3, 80 3, 117 7" stroke="#C94309" stroke-width="2.5" stroke-linecap="round" style="stroke-dashoffset: 0px; stroke-dasharray: 114.391;"></path>
                          </svg>
                        </span>
                      </span>
 
                    <b class="font-bold"> تبيع عنّك،</b>
                     بدقائق.
                </h1>
                <p class="text-base md:text-lg text-[#666666] leading-relaxed max-w-[35rem] font-normal">
                     أنشئ صفحة لأعمالك، تستقبل الزوار، تجيب عن أسئلتهم، تعرض المنتجات والخدمات، وتستقبل الطلبات والحجوزات على مدار الساعة.
                </p>
                <p class="font-thin -mt-4 opacity-60 max-w-[35rem]">
                    حوّل كل زيارة إلى فرصة للبيع، أو الحجز، أو التواصل، من خلال صفحة أعمال احترافية تعزز ثقة عملائك وتبرز علامتك التجارية.
                </p>
                <div class="">
                    <a href="{{ route('auth.register') }}" wire:navigate class="inline-flex items-center justify-center px-8 py-4 rounded-full bg-gray-900 text-white text-lg font-medium hover:bg-gray-800   Xhover:-translate-y-0.5 transition-all duration-300 group">
                        أنشئ صفحتي الآن، مجاناً
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-right w-6 h-6 ms-3 group-hover:-translate-x-1 transition-transform -rotate-180"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>
                    
                    {{-- <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-1 mt-6">
                        <span class="bg-zinc-900 text-white text-lg font-medium inline-flex items-center justify-center h-14 px-8 px-6 py-3 rounded-full transition-all duration-300 group-hover:bg-primary-700 group-hover:pr-8">
                            أنشئ صفحتي الآن، مجاناً
                        </span>
                        <span class="w-14 h-14 rounded-full bg-zinc-900 text-white flex items-center justify-center transition-all duration-300 rotate-[-135deg] group-hover:bg-primary-700 group-hover:rotate-[-130deg]">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" aria-hidden="true" class="lucide lucide-arrow-up-right w-4 h-4"><path d="M7 7h10v10"></path><path d="M7 17 17 7"></path></svg>
                        </span>
                    </a> --}}

                    {{-- <a href="#waitlist" class="inline-flex items-center justify-center h-14 px-8 rounded-full bg-[#111111] text-white text-lg font-normal hover:bg-[#333333] transition-colors">
                        أنشئ صفحتي الآن، مجاناً
                    </a> --}}

                    <p class="mt-5 text-sm text-[#666666] font-normal flex items-center gap-4">
                        <span class="inline-flex items-center gap-1">
                            <iconify-icon icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-700 text-base"></iconify-icon>
                            مجانًا 
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <iconify-icon icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-700 text-base"></iconify-icon>
                            بدون خبرة تقنية
                        </span>
                        <span class="inline-flex items-center gap-1">
                          <iconify-icon icon="solar:check-circle-bold" stroke-width="1.5" class="text-green-700 text-base"></iconify-icon>
                          جاهزة خلال دقائق
                        </span>
                    </p>
                </div>

            </div>

            
        </div>
    </section>



    <div class="gridx grid-cols-2x md:grid-cols-7x flex itmes-center justify-center bg-[#0B161B] text-white border-t border-[#262626]">
        <div class="w-full border-r border-[#262626] p-6 flex flex-col gap-8 group hover:bg-[#141414] hover:text-[#F97316] transition-colors cursor-pointer">
          <div class="flex justify-between items-start">
            <iconify-icon icon="solar:bed-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
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
            <iconify-icon icon="solar:cup-hot-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
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
            <iconify-icon icon="solar:dumbbell-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
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
            <iconify-icon icon="solar:camera-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
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
            <iconify-icon icon="solar:wineglass-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
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
            <iconify-icon icon="solar:medal-star-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
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
            <iconify-icon icon="solar:medal-star-linear" width="24" class="text-[#979797] group-hover:text-[#F97316]"></iconify-icon>
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



    <div class="w-full overflow-hiddenx border-y border-black/5 bg-white py-4 reveal-up active">
        <div class="animate-marquee w-full overflow-x-auto scroll-hide flex justify-center scroll-smooth  whitespace-nowrap flex gap-12 items-center text-xs font-normal   text-[#888888]">
            <span>Accept payments globally</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Cards &amp; Mobile Money</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Stablecoin native</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Settle in NGN, GHS, KES, ZAR, USD</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Accept payments globally</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Cards &amp; Mobile Money</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Stablecoin native</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Settle in NGN, GHS, KES, ZAR, USD</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>            
            <span>Settle in NGN, GHS, KES, ZAR, USD</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Accept payments globally</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Cards &amp; Mobile Money</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Stablecoin native</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
            <span>Settle in NGN, GHS, KES, ZAR, USD</span>
            <span class="w-1.5 h-1.5 rounded-full bg-black/10"></span>
        </div>
    </div>


    <section class="bg-white border-b border-gray-100 py-16 lg:py-40 px-3">
        <div class="max-w-7xl mx-auto relative">
            <div class="grid lg:grid-cols-12 gap-12">
             
              <div class="lg:col-span-9">
                <p data-reveal="" class="text-base tracking-widest text-primary-500 flex items-center gap-2 mb-4" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    <span class="w-6 h-px bg-primary-500"></span>
                    01
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl  !leading-[4rem] max-w-2xl tracking-tight" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    {{-- — --}}
                    صفحة تحوّل
                    <b class="font-bold"> زوارك  إلى عملاء،</b>
                     
                     حتى وأنت نائم.
                </h2>
                <div class="gridX sm:grid-cols-2X max-w-2xl gap-6 mt-8 items-center">
                 
                  <div>
                    <p class="text-lg text-zinc-600 leading-relaxed ">
                        اجعل كل زيارة فرصة للبيع أو الحجز أو التواصل، من خلال تجربة واضحة تقود العميل إلى اتخاذ الإجراء المناسب دون تشتت.
                    </p>
                  
                    <div class="flex flex-wrap gap-2  mt-10 mb-2">
                        <!-- Pill items -->
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🛒</span> <span class="text-sm">متجر إلكتروني</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📅</span> <span class="text-sm">حجز المواعيد</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🧾</span> <span class="text-sm">طلب الخدمات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">💳</span> <span class="text-sm">الدفع الإلكتروني</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📥</span> <span class="text-sm">استقبال الطلبات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📦</span> <span class="text-sm">المنتجات الرقمية</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🏠</span> <span class="text-sm">تأجير الوحدات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🎯</span> <span class="text-sm">أزرار اتخاذ الإجراء (CTA)</span>
                        </div>
                    </div>
                

                    <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-px mt-6">
                        <span class="bg-zinc-900 text-white text-sm font-medium inline-flex items-center justify-center px-5 py-2.5 rounded-full transition-all duration-300 group-hover:bg-primary-700 xgroup-hover:pr-8">
                            أنشئ صفحتي الآن، مجاناً
                        </span>
                        <span class="size-10 rounded-full bg-zinc-900 text-white flex items-center justify-center transition-all duration-300 rotate-[-135deg] group-hover:bg-primary-700 group-hover:rotate-[-130deg]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" aria-hidden="true" class="lucide lucide-arrow-up-right w-4 h-4"><path d="M7 7h10v10"></path><path d="M7 17 17 7"></path></svg>
                        </span>
                    </a>

                    

                   
                  </div>

                   
                </div>

               
              </div>
              <div>.</div>
            </div>
        </div>
    </section>


    

    <section class="bg-white/80 border-b border-gray-100 py-16 lg:py-40 px-3">
        <div class="max-w-7xl mx-auto relative">
            <div class="grid lg:grid-cols-12 gap-12">
             
              <div class="lg:col-span-9">
                <p data-reveal="" class="text-base tracking-widest text-orange-500 flex items-center gap-2 mb-4" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    <span class="w-6 h-px bg-orange-500"></span>
                    02
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl  !leading-[4rem] max-w-2xl tracking-tight" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    {{-- — --}}
                    صفحة
                    <b class="font-bold"> ترسّخ علامتك التجارية</b>.
                      
                </h2>
                <div class="gridX sm:grid-cols-2X max-w-2xl gap-6 mt-8 items-center">
                 
                  <div >
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        أنشئ صفحة تحمل هويتك بالكامل، وتمنح عملاءك تجربة متناسقة واحترافية في كل زيارة.
                    </p>
                    <p class="text-lg text-zinc-400 leading-relaxed">
                        خصص كل تفاصيل الصفحة لتبدو امتدادًا لعلامتك، من الدومين والألوان إلى أسلوب العرض والتواصل.
                    </p>

                    <div class="flex flex-wrap gap-2  mt-10 mb-2">
                        <!-- Pill items -->
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🌐</span> <span class="text-sm">دومين مخصص</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">✉️</span> <span class="text-sm">بريد إلكتروني رسمي</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🎨</span> <span class="text-sm">ألوان هويتك</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🖋️</span> <span class="text-sm">الخطوط والشعار</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📱</span> <span class="text-sm">تصميم متوافق مع الجوال</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">✨</span> <span class="text-sm">واجهات احترافية</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🔗</span> <span class="text-sm">رابط واحد دائم</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🌍</span> <span class="text-sm">دعم لغات متعددة</span>
                        </div>
                    </div>
                    
                    <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-1 ">
                         
                        <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-px mt-6">
                            <span class="bg-zinc-900 text-white text-sm font-medium inline-flex items-center justify-center px-5 py-2.5 rounded-full transition-all duration-300 group-hover:bg-primary-700 xgroup-hover:pr-8">
                                أنشئ صفحتي الآن، مجاناً
                            </span>
                            <span class="size-10 rounded-full bg-zinc-900 text-white flex items-center justify-center transition-all duration-300 rotate-[-135deg] group-hover:bg-primary-700 group-hover:rotate-[-130deg]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" aria-hidden="true" class="lucide lucide-arrow-up-right w-4 h-4"><path d="M7 7h10v10"></path><path d="M7 17 17 7"></path></svg>
                            </span>
                        </a>
 
                    </a>
                  </div>
                </div>
              </div>
              <div>.</div>
            </div>
        </div>
    </section>


    

    

    <section class="bg-white border-b border-gray-100 py-16 lg:py-40 px-3">
        <div class="max-w-7xl mx-auto relative">
            <div class="grid lg:grid-cols-12 gap-12">
             
              <div class="lg:col-span-9">
                <p data-reveal="" class="text-base tracking-widest text-cyan-500 flex items-center gap-2 mb-4" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    <span class="w-6 h-px bg-cyan-500"></span>
                    03
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl  !leading-[4rem] max-w-2xl tracking-tight" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    {{-- — --}}
                    صفحة
                    <b class="font-bold"> تعزز ثقة عملائك</b>
                       بك.
                      
                </h2>
                <div class="gridX sm:grid-cols-2X max-w-2xl gap-6 mt-8 items-center">
                 
                  <div >
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        أنشئ صفحة تحمل هويتك بالكامل، وتمنح عملاءك تجربة متناسقة واحترافية في كل زيارة.
                    </p>
                    <p class="text-lg text-zinc-400 leading-relaxed">
                        خصص كل تفاصيل الصفحة لتبدو امتدادًا لعلامتك، من الدومين والألوان إلى أسلوب العرض والتواصل.
                    </p>

                    <div class="flex flex-wrap gap-2  mt-10 mb-2">
                        <!-- Pill items -->
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">👤</span> <span class="text-sm">منطقة العميل</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">⭐</span> <span class="text-sm">تقييمات العملاء</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🏆</span> <span class="text-sm">الشهادات والاعتمادات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🖼️</span> <span class="text-sm">معرض الأعمال</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🛡️</span> <span class="text-sm">الضمانات والسياسات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📦</span> <span class="text-sm">متابعة الطلبات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🧾</span> <span class="text-sm">الفواتير والمشتريات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">❓</span> <span class="text-sm">الأسئلة الشائعة</span>
                        </div>
                        
                    </div>

                    <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-1 mt-8">
                         
                        <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-px mt-6">
                            <span class="bg-zinc-900 text-white text-sm font-medium inline-flex items-center justify-center px-5 py-2.5 rounded-full transition-all duration-300 group-hover:bg-primary-700 xgroup-hover:pr-8">
                                أنشئ صفحتي الآن، مجاناً
                            </span>
                            <span class="size-10 rounded-full bg-zinc-900 text-white flex items-center justify-center transition-all duration-300 rotate-[-135deg] group-hover:bg-primary-700 group-hover:rotate-[-130deg]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" aria-hidden="true" class="lucide lucide-arrow-up-right w-4 h-4"><path d="M7 7h10v10"></path><path d="M7 17 17 7"></path></svg>
                            </span>
                        </a>
 
                    </a>
                  </div>
                </div>
              </div>
              <div>.</div>
            </div>
        </div>
    </section>


    
    

    

    <section class="bg-white/80 border-b border-gray-100 py-16 lg:py-40 px-3">
        <div class="max-w-7xl mx-auto relative">
            <div class="gridX sm:grid-cols-2X max-w-2xl gap-6 mt-8 items-center">
             
              <div class="lg:col-span-9">
                <p data-reveal="" class="text-base tracking-widest text-amber-500 flex items-center gap-2 mb-4" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    <span class="w-6 h-px bg-amber-500"></span>
                    04
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl  !leading-[4rem] max-w-2xl tracking-tight" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    {{-- — --}}
                    صفحة
                    <b class="font-bold"> تُبقي علامتك حاضرة.</b>
                </h2>
                <div class="grid  gap-6 mt-8 items-center">
                 
                <div>
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        حوّل كل محتوى تنشره إلى فرصة جديدة لبناء الوعي، وتعزيز الثقة، وجذب عملاء جدد.
                    </p>
                    <p class="text-lg text-zinc-400 leading-relaxed">
                         اجعل المحتوى يعمل لصالحك؛ انشر مقالاتك، ونشراتك البريدية، وفيديوهاتك، في مكان واحد، ليبقى نشاطك حاضرًا في أذهان عملائك ويعودوا إليك مرة بعد أخرى.             
                   </p>

                    <div class="flex flex-wrap gap-2  mt-10 mb-2">
                        <!-- Pill items -->
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">✍️</span> <span class="text-sm">المدونة</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📧</span> <span class="text-sm">النشرة البريدية</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🎥</span> <span class="text-sm">الفيديوهات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🎙️</span> <span class="text-sm">البودكاست</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">▶️</span> <span class="text-sm">قوائم التشغيل</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📚</span> <span class="text-sm">المقالات والدلائل</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📢</span> <span class="text-sm">آخر الأخبار</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🔔</span> <span class="text-sm">تحديثات النشاط</span>
                        </div>
                    </div>

                    <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-1 mt-8">
                         
                        <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-px mt-6">
                            <span class="bg-zinc-900 text-white text-sm font-medium inline-flex items-center justify-center px-5 py-2.5 rounded-full transition-all duration-300 group-hover:bg-primary-700 xgroup-hover:pr-8">
                                أنشئ صفحتي الآن، مجاناً
                            </span>
                            <span class="size-10 rounded-full bg-zinc-900 text-white flex items-center justify-center transition-all duration-300 rotate-[-135deg] group-hover:bg-primary-700 group-hover:rotate-[-130deg]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" aria-hidden="true" class="lucide lucide-arrow-up-right w-4 h-4"><path d="M7 7h10v10"></path><path d="M7 17 17 7"></path></svg>
                            </span>
                        </a>
 
                    </a>
                  </div>
                </div>
              </div>
              <div>.</div>
            </div>
        </div>
    </section>


        
    

    <section class="bg-white   py-16 lg:py-40 px-3">
        <div class="max-w-7xl mx-auto relative">
            <div class="gridX sm:grid-cols-2X max-w-2xl gap-6 mt-8 items-center">
             
              <div class="lg:col-span-9">
                <p data-reveal="" class="text-base tracking-widest text-lime-500 flex items-center gap-2 mb-4" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    <span class="w-6 h-px bg-lime-500"></span>
                    05
                  </p>
                <h2 data-reveal="" class="text-4xl sm:text-5xl  !leading-[4rem] max-w-2xl tracking-tight" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    {{-- — --}}
                    صفحة
                    <b class="font-bold"> تنمو  .</b>
                      مع نشاطك.
                      
                </h2>
                <div class="grid  gap-6 mt-8 items-center">
                 
                  <div >
                    <p class="text-lg text-zinc-600 leading-relaxed">
                        ابدأ بما يحتاجه نشاطك اليوم، وأضف المزيد كلما توسعت أعمالك. من متجر وحجوزات إلى محتوى وفريق عمل وتكاملات، كل شيء جاهز لينمو معك دون الحاجة إلى البدء من جديد.
                   </p>
              

                    <div class="flex flex-wrap gap-2  mt-10 mb-2">
                        <!-- Pill items -->
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🧩</span> <span class="text-sm">أضف أقسامًا جديدة</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📅</span> <span class="text-sm">توسّع بخدمات جديدة</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">👥</span> <span class="text-sm">إدارة الفريق</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">📊</span> <span class="text-sm">إحصاءات الأداء</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">⚡</span> <span class="text-sm">تكاملات خارجية</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🔌</span> <span class="text-sm">ربط التطبيقات</span>
                        </div>
                        <div class="px-4 py-2 xborder border-black/10 rounded-md flex items-center gap-2 text-base bg-stone-200 hover:bg-stone-300/80 hover:border-black/20 transition-colors interactive cursor-hover">
                            <span class="text-base leading-none">🚀</span> <span class="text-sm">تحديثات مستمرة</span>
                        </div>
                    </div>

                    <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-1 mt-8">
                         
                        <a href="{{ route('auth.register') }}" wire:navigate class="group inline-flex items-center gap-px mt-6">
                            <span class="bg-zinc-900 text-white text-sm font-medium inline-flex items-center justify-center px-5 py-2.5 rounded-full transition-all duration-300 group-hover:bg-primary-700 xgroup-hover:pr-8">
                                أنشئ صفحتي الآن، مجاناً
                            </span>
                            <span class="size-10 rounded-full bg-zinc-900 text-white flex items-center justify-center transition-all duration-300 rotate-[-135deg] group-hover:bg-primary-700 group-hover:rotate-[-130deg]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" aria-hidden="true" class="lucide lucide-arrow-up-right w-4 h-4"><path d="M7 7h10v10"></path><path d="M7 17 17 7"></path></svg>
                            </span>
                        </a>
 
                    </a>
                  </div>
                </div>
              </div>
              <div>.</div>
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
                ثلاث باقات... ونقطة بداية واحدة.
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
            <div data-reveal="" class="liquid-border relative rounded-3xl bg-white/70 backdrop-blur-xl p-8 flex flex-col hover:-translate-y-2 transition-transform duration-300 shadow-[0_24px_60px_-24px_rgba(15,118,110,0.18),0_4px_12px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_32px_70px_-24px_rgba(15,118,110,0.28)]" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
              <div class="flex items-center gap-3 mb-8">
                <span class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="feather" aria-hidden="true" class="lucide lucide-feather w-5 h-5 text-primary-700"><path d="M12.67 19a2 2 0 0 0 1.416-.588l6.154-6.172a6 6 0 0 0-8.49-8.49L5.586 9.914A2 2 0 0 0 5 11.328V18a1 1 0 0 0 1 1z"></path><path d="M16 8 2 22"></path><path d="M17.5 15H9"></path></svg>
                </span>
                <p class="font-medium">بداية</p>
              </div>
              <p class="text-5xl font-medium tracking-tight">
                $950
                <span class="text-base font-normal text-zinc-500">/mo</span>
              </p>
              <p class="text-sm text-zinc-500 mt-3 leading-relaxed">
                 ابدأ مجانًا، ثم طوّر باقتك مع نمو نشاطك.
                
              </p>
              <ul class="space-y-3 mt-8 mb-8 text-sm text-zinc-600 flex-1">
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-600"><path d="M20 6 9 17l-5-5"></path></svg>
                  Brand identity kit
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-600"><path d="M20 6 9 17l-5-5"></path></svg>
                  One landing page
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-600"><path d="M20 6 9 17l-5-5"></path></svg>
                  2 revision rounds
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-600"><path d="M20 6 9 17l-5-5"></path></svg>
                  Async support
                </li>
              </ul>
              <a href="#contact" class="inline-flex items-center justify-center rounded-full border border-zinc-300 py-3 text-sm font-medium hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition-all duration-300">
                Choose Launch
              </a>
            </div>
            <div data-reveal="" class="liquid-border liquid-border-dark relative rounded-3xl bg-zinc-900 text-white p-8 flex flex-col hover:-translate-y-2 transition-transform duration-300 overflow-hidden shadow-[0_28px_70px_-24px_rgba(20,184,166,0.35),0_8px_20px_-8px_rgba(0,0,0,0.4)] hover:shadow-[0_36px_80px_-24px_rgba(20,184,166,0.45)]" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
              <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-primary-500/30 blur-3xl pointer-events-none"></div>
              <div class="relative flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                  <span class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="sparkles" aria-hidden="true" class="lucide lucide-sparkles w-5 h-5 text-white"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path><path d="M20 2v4"></path><path d="M22 4h-4"></path><circle cx="4" cy="20" r="2"></circle></svg>
                  </span>
                  <p class="font-medium">انطلاق</p>
                </div>
                <span class="text-[10px] tracking-widest bg-primary-500 text-zinc-900 font-semibold px-3 py-1 rounded-full">
                  POPULAR
                </span>
              </div>
              <p class="relative text-5xl font-medium tracking-tight">
                $1,450
                <span class="text-base font-normal text-zinc-400">/mo</span>
              </p>
              <p class="relative text-sm text-zinc-400 mt-3 leading-relaxed">
                كل ما تحتاجه لإطلاق صفحة أعمال احترافية متكاملة.
                
              </p>
              <ul class="relative space-y-3 mt-8 mb-8 text-sm text-zinc-300 flex-1">
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-400"><path d="M20 6 9 17l-5-5"></path></svg>
                  Everything in Launch
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-400"><path d="M20 6 9 17l-5-5"></path></svg>
                  Full website design
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-400"><path d="M20 6 9 17l-5-5"></path></svg>
                  Motion &amp; interaction
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-400"><path d="M20 6 9 17l-5-5"></path></svg>
                  Unlimited requests
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-400"><path d="M20 6 9 17l-5-5"></path></svg>
                  48h turnaround
                </li>
              </ul>
              <a href="#contact" class="relative inline-flex items-center justify-center rounded-full bg-primary-500 text-zinc-900 py-3 text-sm font-semibold hover:bg-primary-300 transition-colors duration-300">
                Choose Studio
              </a>
            </div>
            <div data-reveal="" class="liquid-border relative rounded-3xl bg-white/70 backdrop-blur-xl p-8 flex flex-col hover:-translate-y-2 transition-transform duration-300 shadow-[0_24px_60px_-24px_rgba(15,118,110,0.18),0_4px_12px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_32px_70px_-24px_rgba(15,118,110,0.28)]" style="filter: blur(0px); translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
              <div class="flex items-center gap-3 mb-8">
                <span class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="gem" aria-hidden="true" class="lucide lucide-gem w-5 h-5 text-primary-700"><path d="M10.5 3 8 9l4 13 4-13-2.5-6"></path><path d="M17 3a2 2 0 0 1 1.6.8l3 4a2 2 0 0 1 .013 2.382l-7.99 10.986a2 2 0 0 1-3.247 0l-7.99-10.986A2 2 0 0 1 2.4 7.8l2.998-3.997A2 2 0 0 1 7 3z"></path><path d="M2 9h20"></path></svg>
                </span>
                <p class="font-medium">نمو</p>
              </div>
              <p class="text-5xl font-medium tracking-tight">
                $2,900
                <span class="text-base font-normal text-zinc-500">/mo</span>
              </p>
              <p class="text-sm text-zinc-500 mt-3 leading-relaxed">
                مزايا متقدمة لإدارة نشاطك وبناء حضور رقمي يدوم.
              </p>
              <ul class="space-y-3 mt-8 mb-8 text-sm text-zinc-600 flex-1">
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-600"><path d="M20 6 9 17l-5-5"></path></svg>
                  Everything in Studio
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-600"><path d="M20 6 9 17l-5-5"></path></svg>
                  Product strategy
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-600"><path d="M20 6 9 17l-5-5"></path></svg>
                  Dedicated art director
                </li>
                <li class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-4 h-4 text-primary-600"><path d="M20 6 9 17l-5-5"></path></svg>
                  Quarterly brand audits
                </li>
              </ul>
              <a href="#contact" class="inline-flex items-center justify-center rounded-full border border-zinc-300 py-3 text-sm font-medium hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition-all duration-300">
                Choose Partner
              </a>
            </div>
          </div>
        </div>
      </section>



      <section class="py-24  hidden">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="inline-flex items-center gap-2 text-xs font-medium bg-black/5 text-[#0E0E0E] px-3 py-1.5 rounded-full mb-6">
                <iconify-icon icon="solar:eye-closed-linear" class="text-[#6B7280]"></iconify-icon>
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
            
            <div class="inline-flex items-center gap-2 text-xs font-medium bg-black/5 text-[#0E0E0E] px-3 py-1.5 rounded-full mb-6">
                <iconify-icon icon="solar:eye-closed-linear" class="text-[#6B7280]"></iconify-icon>
                  أسئلة قد تدور في ذهنك.
            </div>
            <h2 class="text-4xl md:text-5xl tracking-tight uppercase font-normal leading-tight mb-4 text-balance">
                إجابات على أكثر ما يهم أصحاب الأعمال.
            </h2>
            <p class="text-sm text-[#2C2825]/70 max-w-md leading-relaxed">
                أسئلة قد تدور في ذهنك.

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
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-[#C27A63] transition-colors"
                  :class="active === 1 ? 'text-[#C27A63]' : ''"
                >
                  <span class="text-sm md:text-base font-normal flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">01.</span>
                    هل المنصة مناسبة لنشاطي؟
                  </span>
                  <iconify-icon icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 1 ? 'rotate-180' : ''"></iconify-icon>
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
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-[#C27A63] transition-colors"
                  :class="active === 2 ? 'text-[#C27A63]' : ''"
                >
                  <span class="text-sm md:text-base font-normal flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">02.</span>
                    هل أحتاج إلى خبرة تقنية؟
                  </span>
                  <iconify-icon icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 2 ? 'rotate-180' : ''"></iconify-icon>
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
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-[#C27A63] transition-colors"
                  :class="active === 3 ? 'text-[#C27A63]' : ''"
                >
                  <span class="text-sm md:text-base font-normal flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">03.</span>
                    هل أستطيع استخدام دومين خاص وهوية نشاطي؟
                  </span>
                  <iconify-icon icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 3 ? 'rotate-180' : ''"></iconify-icon>
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
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-[#C27A63] transition-colors"
                  :class="active === 4 ? 'text-[#C27A63]' : ''"
                >
                  <span class="text-sm md:text-base font-normal flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">04.</span>
                    هل أستطيع استقبال الطلبات والمدفوعات من الصفحة مباشرة؟
                  </span>
                  <iconify-icon icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 4 ? 'rotate-180' : ''"></iconify-icon>
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
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-[#C27A63] transition-colors"
                  :class="active === 5 ? 'text-[#C27A63]' : ''"
                >
                  <span class="text-sm md:text-base font-normal flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">05.</span>
                    هل سأحتاج إلى إعادة بناء صفحتي عندما يكبر نشاطي؟
                  </span>
                  <iconify-icon icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 5 ? 'rotate-180' : ''"></iconify-icon>
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
                  class="accordion-btn w-full flex justify-between items-center text-start hover:text-[#C27A63] transition-colors"
                  :class="active === 6 ? 'text-[#C27A63]' : ''"
                >
                  <span class="text-sm md:text-base font-normal flex gap-4">
                    <span class="text-[#2C2825]/40 text-xs">06.</span>
                    هل بيانات العملاء والطلبات ملك لي؟
                  </span>
                  <iconify-icon icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="transform transition-transform duration-300" :class="active === 6 ? 'rotate-180' : ''"></iconify-icon>
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




      <footer class="w-full px-6 lg:px-10 pt-16 pb-8 bg-white xborder-t border-[#EBEBEB]">
        <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-8 mb-12">
            <div class="flex flex-col md:flex-row items-center gap-8 lg:gap-12 w-full">
                <a href="{{ route('home') }}" wire:navigate class="text-[#0D0C22] shrink-0 flex items-center gap-2 font-brand font-medium text-xl tracking-tight" aria-label="{{ config('app.name') }}">
                    <img class="w-auto h-7" src="{{ asset('assets/images/logo.webp') }}" alt="{{ config('app.name') }}" />
                </a>
                <ul class="flex flex-wrap justify-center md:justify-start gap-6 lg:gap-8 text-base font-medium text-[#0D0C22]">
                    <li><a href="#pricing" class="hover:text-gray-600 transition-colors">الباقات والأسعار</a></li>
                    <li><a href="#faq" class="hover:text-gray-600 transition-colors">الأسئلة الشائعة</a></li>
                    <li><a href="{{ route('auth.register') }}" wire:navigate class="hover:text-gray-600 transition-colors">أنشئ صفحتك</a></li>
                    <li><a href="{{ route('auth.login') }}" wire:navigate class="hover:text-gray-600 transition-colors">تسجيل الدخول</a></li>
                </ul>
            </div>
            <div class="flex items-center gap-4 shrink-0 text-[#0D0C22]">
                <a href="https://x.com/broshurcom" target="_blank" rel="noopener noreferrer" class="hover:text-gray-600 transition-colors" aria-label="X"><iconify-icon icon="solar:hashtag-linear" class="w-5 h-5"></iconify-icon></a>
                <a href="{{ route('home') }}" wire:navigate class="hover:text-gray-600 transition-colors" aria-label="{{ config('app.name') }}"><iconify-icon icon="solar:global-linear" class="w-5 h-5"></iconify-icon></a>
                <a href="https://www.instagram.com/broshurcom" target="_blank" rel="noopener noreferrer" class="hover:text-gray-600 transition-colors" aria-label="إنستغرام"><iconify-icon icon="solar:camera-linear" class="w-5 h-5"></iconify-icon></a>
                <a href="{{ route('auth.register') }}" wire:navigate class="hover:text-gray-600 transition-colors" aria-label="ابدأ الآن"><iconify-icon icon="solar:play-circle-linear" class="w-5 h-5"></iconify-icon></a>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm font-normal text-[#6E6D7A]">
            <div class="flex items-center gap-4">
                <span>{{ config('app.name') }} © {{ date('Y') }}</span>
                <a href="#" class="hover:text-[#0D0C22]">الشروط والأحكام</a>
                <a href="#" class="hover:text-[#0D0C22]">سياسة الخصوصية</a>
                <a href="#" class="hover:text-[#0D0C22]">ملفات تعريف الارتباط</a>
            </div>
            <div class="flex items-center gap-4 flex-wrap justify-center">
                <a href="{{ route('auth.register') }}" wire:navigate class="hover:text-[#0D0C22]">أنشئ صفحتك</a>
                <a href="{{ route('auth.login') }}" wire:navigate class="hover:text-[#0D0C22]">تسجيل الدخول</a>
                <a href="#pricing" class="hover:text-[#0D0C22]">الباقات</a>
                <a href="#faq" class="hover:text-[#0D0C22]">الأسئلة الشائعة</a>
                <a href="#faq" class="hover:text-[#0D0C22]">الدعم والمساعدة</a>
                <a href="{{ route('admin.home') }}" wire:navigate class="hover:text-[#0D0C22]">لوحة التحكم</a>
            </div>
        </div>
    </footer>

</div>

<?php 
 
new 
#[\Livewire\Attributes\Title('أنشئ صفحة لأعمالك، تبيع عنّك بدقائق!')]
class extends \Livewire\Component {
     
}; ?>
