<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}" class="">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? '' }} | {{tenant()->name}}</title>

        <script src="{{ asset('assets/js/twind.min.js') }}"></script>
        <script src="{{ asset('assets/js/twind.custom.js') }}"></script>
 
        <script src="https://code.iconify.design/iconify-icon/2.3.0/iconify-icon.min.js"></script>
 
        <script>
            let config = { ...@js(config('twind')), ...customTwindconf };
            twind.install(config);
        </script>
        
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
    <body class="">
        {{ $slot }}

        @stack('scripts')
        @livewireScripts
    </body>
</html>
