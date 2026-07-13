<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}" class="">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? '' }} | {{tenant()->name}}</title>

        <link rel="icon" type="image/png" href="{{ tenant('logo') }}">

        <script src="{{ asset('assets/js/twind.min.js') }}"></script>
        <script src="{{ asset('assets/js/twind.custom.js') }}"></script>
 
        <script src="https://code.iconify.design/iconify-icon/2.3.0/iconify-icon.min.js"></script>
 
        <link rel="stylesheet" href="{{ asset('assets/vendor/moyasar/moyasar.css') }}">
        <script src="{{ asset('assets/vendor/moyasar/moyasar.js') }}"></script>

        <script>
            let config = { ...@js(config('twind')), ...customTwindconf };
            config.theme = config.theme || {};
            config.theme.extend = config.theme.extend || {};
            config.theme.extend.colors = {
                ...(config.theme.extend.colors || {}),
                primary: @js($themePrimaryPalette ?? config('twind.theme.extend.colors.primary')),
            };
            twind.install(config);
        </script>
        
        <style>
            html {
                scroll-behavior: smooth;
                @if (filled(theme_option('fontFamily')))
                    font-family: {{ theme_option('fontFamily') }}, ibmps, sans-serif;
                @endif
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
