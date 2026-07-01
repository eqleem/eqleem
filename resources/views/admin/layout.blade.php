<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}" class="">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }} | {{ tenant('name') }}</title>

        <script src="{{ asset('assets/js/twind.min.js') }}"></script>
        <script src="{{ asset('assets/js/twind.custom.js') }}"></script>
 
        <script src="https://code.iconify.design/iconify-icon/2.3.0/iconify-icon.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
 
        <script>
            let config = { ...@js(config('twind')), ...customTwindconf };
            twind.install(config);
        </script>

        {{ Vite::useBuildDirectory('build')->withEntryPoints(['resources/css/app.css', 'resources/js/app.js']) }}

        
        <style>
            html {
                scroll-behavior: smooth;
            }

            [x-cloak] {
                display: none !important;
            }
        </style>
        @livewireStyles
    </head>
    <body class="antialiased bg-stone-200 ">
        <ui:notify />
        <x-admin::header />
        <x-admin::navbar />

        <main class="min-h-[80vh]">    
            {{ $slot }}
        </main>


        <div class="text-center text-stone-400 pb-4 mx-auto flex items-center justify-center">
            {{ date('Y') }} © 
            {{-- {{ config('app.name') }} --}}
            <a href="https://eqleem.com" target="_blank" title="إقليم" aria-label="إقليم"
                class="text-stone-500 hover:text-stone-600 inline-block mx-2">
                <img class="h-6 w-auto" src="{{ asset('assets/images/logo-text-black.webp') }}" alt="إقليم"
                    title="إقليم">
            </a>
        </div>


        @stack('scripts')
        @livewireScripts


    </body>
</html>
