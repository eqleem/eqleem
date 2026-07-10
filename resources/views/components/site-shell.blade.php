@props([
    'title' => null,
    'subtitle' => null,
])

<div class="min-h-screen relative bg-stone-200 text-[#111111] antialiased overflow-x-hidden selection:bg-[#EAEAEA] selection:text-[#111111] flex flex-col">
    <nav class="top-0 w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-3 md:px-2 xl:px-0 h-[4.5rem] md:h-[5rem] flex items-center justify-between">
            <a href="{{ route('home') }}" wire:navigate class="font-display text-xl font-medium tracking-tighter text-[#111111] flex items-center gap-2">
                <img class="w-auto h-7 md:h-8" src="{{ asset('assets/images/logo.webp') }}" alt="{{ config('app.name') }}" />
            </a>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('admin.home') }}" wire:navigate class="inline-flex items-center justify-center h-10 px-5 shrink-0 rounded-full bg-[#111111] text-white text-sm font-normal hover:bg-[#333333] transition-colors">
                        لوحة التحكم
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 ms-3 -rotate-180" aria-hidden="true"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>
                @else
                    <a href="{{ route('auth.register') }}" wire:navigate class="inline-flex items-center justify-center h-10 px-5 shrink-0 rounded-full bg-[#111111] text-white text-sm font-normal hover:bg-[#333333] transition-colors">
                        <span class="inline md:hidden">تسجيل</span>
                        <span class="hidden md:inline">أنشئ صفحتي الآن</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 ms-3 -rotate-180" aria-hidden="true"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>
                    <a href="{{ route('auth.login') }}" wire:navigate class="inline-flex items-center justify-center p-2 px-4 rounded-full border border-black/10 text-[#111111] text-sm font-normal hover:bg-black/5 transition-colors gap-2">
                        دخول
                        <iconify-icon icon="solar:arrow-left-linear" class="hidden md:block"></iconify-icon>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-1 w-full px-6 lg:px-10 pb-20">
        <div class="max-w-3xl mx-auto pt-8 lg:pt-12">
            @if ($title)
                <p class="text-xs tracking-widest text-stone-500 flex items-center gap-2 mb-4">
                    <span class="w-6 h-px bg-stone-500"></span>
                    {{ config('app.name') }}
                </p>
                <h1 class="text-3xl md:text-4xl tracking-tighter text-[#111111] mb-3">{{ $title }}</h1>
            @endif
            @if ($subtitle)
                <p class="text-base md:text-lg text-stone-500 leading-relaxed mb-10">{{ $subtitle }}</p>
            @endif

            {{ $slot }}
        </div>
    </main>

    <footer class="w-full px-6 lg:px-10 pt-16 pb-8 bg-white border-[#EBEBEB]">
        <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-8 mb-12">
            <div class="flex flex-col md:flex-row items-center gap-8 lg:gap-12 w-full">
                <a href="{{ route('home') }}" wire:navigate class="text-[#0D0C22] shrink-0 flex items-center gap-2 font-brand font-medium text-xl tracking-tight" aria-label="{{ config('app.name') }}">
                    <img class="w-auto h-7" src="{{ asset('assets/images/logo.webp') }}" alt="{{ config('app.name') }}" />
                </a>
                <ul class="flex flex-wrap justify-center md:justify-start gap-6 lg:gap-8 text-base font-medium text-[#0D0C22]">
                    <li><a href="{{ route('home') }}#pricing" wire:navigate class="hover:text-gray-600 transition-colors">الباقات والأسعار</a></li>
                    <li><a href="{{ route('home') }}#faq" wire:navigate class="hover:text-gray-600 transition-colors">الأسئلة الشائعة</a></li>
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
            <div class="flex items-center gap-4 flex-wrap justify-center">
                <a href="{{ route('terms') }}" wire:navigate class="hover:text-[#0D0C22]">الشروط والأحكام</a>
                <a href="{{ route('privacy') }}" wire:navigate class="hover:text-[#0D0C22]">سياسة الخصوصية</a>
                <a href="{{ route('contact') }}" wire:navigate class="hover:text-[#0D0C22]">اتصل بنا</a>
            </div>
            <div class="flex items-center gap-4">
                <span>
                    <span class="hidden lg:inline">{{ config('app.name') }}</span>
                    © {{ date('Y') }}
                </span>
            </div>
        </div>
    </footer>
</div>
