{{-- <section class="text-emerald-50 bg-gray-900 w-full border-emerald-900/60 border-t">
    <div class=" px-4 sm:px-6 lg:px-8 pt-24 pb-5 ">
      <div class=" max-w-6xl mx-auto flex flex-col lg:flex-row lg:items-start lg:justify-between gap-10 mb-10">
        <div class="max-w-xl">
          <h2 class="font-playfair text-4xl sm:text-5xl  leading-tight tracking-tight">
            صفحات
            <span class="italicX text-emerald-300"> تبيع عنّك </span>
          </h2>
          <p class="mt-4 font-thin text-sm sm:text-base text-emerald-100/80">
            اطّلع على أمثلة حقيقية لصفحات أنشأها عملاؤنا باستخدام بروشور، بتصاميم احترافية وتجربة مستخدم مدروسة تحقق نتائج فعلية. 
          </p>
        </div>
        <div class="flex flex-col items-start gap-4 max-w-sm">
          <p class="text-xs sm:text-sm text-emerald-200/80">
            خلال دقائق، أنشئ صفحة مجانية لأعمالك تستقبل زوارك وطلبات عملائك.
          </p>
          <a href="{{ route('auth.register') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full bg-emerald-400 text-emerald-950 px-4 py-2 text-xs sm:text-sm font-medium hover:bg-emerald-300 transition-colors">
            أنشئ صفحتك الآن 
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 rotate-180" viewBox="0 0 24 24" aria-hidden="true">
              <path fill="currentColor" d="M13.293 5.293a1 1 0 0 1 1.414 0l4 4a.997.997 0 0 1 .083.094l.007.01l.007.01a.997.997 0 0 1 .083.148l.003.01l.005.01A1 1 0 0 1 19.999 11v.003a1 1 0 0 1-.293.704l-4 4a1 1 0 0 1-1.414-1.414L16.586 12H6a1 1 0 1 1 0-2h10.586l-3.293-3.293a1 1 0 0 1 0-1.414Z"></path>
            </svg>
          </a>
        </div>
      </div>
    </div>
</section> --}}

<div class="flex flex-col items-center justify-top antialiased selection:bg-orange-500/30 text-white bg-black">
 
    <!-- Fixed Navigation -->
    <nav class="fixedX w-full top-0 left-0 right-0 z-50 flex items-center justify-between px-4 md:px-6 bg-black/80 backdrop-blur-md border-b border-white/10 h-16">
        <!-- Left: Title -->
        <div class="flex items-center gap-2 w-auto md:w-1/3">
            <span class="font-display text-sm font-semibold tracking-tight text-white font-geist"> صفحات العملاء </span>
        </div>

        <!-- Center: Pagination Dots (Hidden on small mobile, visible on sm+) -->
        <div id="pagination-dots" class="hidden sm:flex items-center justify-center gap-2 w-1/3" dir="ltr">
            <!-- Initial state: first active, others inactive. JS handles updates. -->
            {{-- <div class="w-1.5 h-1.5 rounded-full bg-white cursor-pointer hover:scale-125 transition-transform" data-index="0"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform" data-index="1"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform" data-index="2"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform" data-index="3"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform" data-index="4"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform" data-index="5"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform" data-index="6"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform" data-index="7"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform" data-index="8"></div> --}}
        </div>

        <!-- Right: Arrows (Hidden on Mobile, use Swipe) -->
        <div class="flex items-center justify-end gap-2 w-1/3">
             <button id="nextBtn" class="p-2 rounded-full hover:bg-white/10 text-neutral-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-5 h-5"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </button>
            <button id="prevBtn" class="p-2 rounded-full hover:bg-white/10 text-neutral-400 hover:text-white transition-colors disabled:opacity-30">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left w-5 h-5"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
            </button>
        </div>
        
        <!-- Mobile Swipe Indicator (Visible only on mobile) -->
        {{-- <div class="md:hidden flex items-center justify-end w-auto text-neutral-500 text-xs gap-1">
             <span class="font-mono">SWIPE</span>
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-move-horizontal"><polyline points="18 8 22 12 18 16"></polyline><polyline points="6 8 2 12 6 16"></polyline><line x1="2" x2="22" y1="12" y2="12"></line></svg>
        </div> --}}
    </nav>

    <!-- Wrapper for the horizontal scroll presentation -->
    <main id="slider" class="flex flex-row overflow-x-auto snap-center snap-mandatory hide-scrollbar w-full p-8 gap-x-3 md:gap-x-6 scroll-smooth" style="mask-image: linear-gradient(90deg, transparent, black 5%, black 95%, transparent); -webkit-mask-image: linear-gradient(90deg, transparent, black 5%, black 95%, transparent);">
  
        <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-0">
            <a href="https://broshur.com/frst1" target="_blank">
                <img src="{{ asset('assets/images/frst.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>  
        </section>
        

        <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-1">
            <a href="https://broshur.com/aswar" target="_blank">
                <img src="{{ asset('assets/images/aswar.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>
        </section>

       <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-2">
            <a href="https://broshur.com/daralzbrj" target="_blank">
                <img src="{{ asset('assets/images/daralzbrj.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>
        </section>

        <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-3">
            <a href="https://broshur.com/baderatgharb" target="_blank">
                <img src="{{ asset('assets/images/baderat.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>
        </section>
      
     
        <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-4">
            <a href="https://broshur.com/qoot" target="_blank">
                <img src="{{ asset('assets/images/qoot.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>
        </section>

        <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-5">
            <a href="https://broshur.com/frst1" target="_blank">
                <img src="{{ asset('assets/images/frst.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>
        </section>

        <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-6">
            <a href="https://broshur.com/aswar" target="_blank">
                <img src="{{ asset('assets/images/aswar.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>
        </section>

        <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-7">
            <a href="https://broshur.com/qoot" target="_blank">
                <img src="{{ asset('assets/images/qoot.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>
        </section>

        <section class="slide-container flex-shrink-0 w-[65vw] lg:w-[25vw] 2xl:w-[20vw] bg-[#0A0A0A] group  rounded-3xl  transition-all duration-300 relative flex flex-col overflow-hidden shadow-2xl snap-center" id="slide-8">
            <a href="https://broshur.com/baderatgharb" target="_blank">
                <img src="{{ asset('assets/images/baderat.webp') }}" alt="" class="w-full h-full rounded-3xl object-top object-contain hover:scale-105 transition-all duration-300">
            </a>
        </section>
  
    </main>

    {{-- <script>
        lucide.createIcons();
    </script> --}}
    
    <!-- Animation & Navigation Logic -->
    <script>
      (function () {
        // --- Intro Animations ---
        const once = true; // Set to true to animate only once

        if (!window.__inViewIO) {
          window.__inViewIO = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                entry.target.classList.add("animate");
                if (once) window.__inViewIO.unobserve(entry.target);
              }
            });
          }, { threshold: 0.2, rootMargin: "0px -10% 0px -10%" });
        }

        window.initInViewAnimations = function (selector = ".animate-on-scroll") {
          document.querySelectorAll(selector).forEach((el) => {
            window.__inViewIO.observe(el); 
          });
        };

        document.addEventListener("DOMContentLoaded", () => {
            initInViewAnimations();
            initPagination();
        });

        // --- Pagination & Slider Logic ---
        function initPagination() {
            const slider = document.getElementById('slider');
            const slides = document.querySelectorAll('.slide-container');
            const dotsContainer = document.getElementById('pagination-dots');
            const dots = dotsContainer.children;
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const isRTL = document.documentElement.getAttribute('dir') === 'rtl' || document.body.getAttribute('dir') === 'rtl';

            if (isRTL && dotsContainer) {
                dotsContainer.classList.add('flex-row-reverse');
            }

            // --- Update Dots Helper ---
            const updateDots = (activeIndex) => {
                Array.from(dots).forEach((dot, index) => {
                    if (index === activeIndex) {
                        dot.className = "w-1.5 h-1.5 rounded-full bg-white cursor-pointer hover:scale-125 transition-transform";
                    } else {
                        dot.className = "w-1.5 h-1.5 rounded-full bg-white/20 hover:bg-white/50 cursor-pointer transition-colors hover:scale-125 transition-transform";
                    }
                });
            };

            // --- Scroll Observer for Dots ---
            // Using the slider as root ensures we track what is currently centered in the scroll area
            const slideObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Find the index based on the ID or element comparison
                        const index = Array.from(slides).indexOf(entry.target);
                        if(index !== -1) {
                            updateDots(index);
                        }
                    }
                });
            }, { 
                root: slider, 
                threshold: 0.6 // High threshold means the slide must be mostly visible to be "active"
            });

            slides.forEach(slide => slideObserver.observe(slide));

            // --- Click on Dot to Scroll ---
            Array.from(dots).forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    slides[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                });
            });

            // --- Arrow Buttons ---
            // Calculate scroll amount dynamically
            const getScrollAmount = () => {
                 // Check if mobile or desktop
                 return window.innerWidth < 768 ? window.innerWidth * 0.85 : 548;
            };

            if(prevBtn && nextBtn && slider) {
                nextBtn.addEventListener('click', () => {
                    slider.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
                });
                
                prevBtn.addEventListener('click', () => {
                    slider.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
                });
            }
        }
      })();
    </script>

 
</div>