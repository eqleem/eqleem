<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}" class="">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-shape-white.webp') }}">

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

            @font-face {
  font-family: "saudi_riyal";
  src: url("assets/fonts/saudi_riyal/saudi_riyal.woff2") format("woff2"),
    url("assets/fonts/saudi_riyal/saudi_riyal.woff") format("woff"),
    url("assets/fonts/saudi_riyal/saudi_riyal.ttf") format("truetype");
  font-weight: normal;
  font-style: normal;
}
 
/* Ensure the icon class correctly loads the Riyal symbol */
.icon-saudi_riyal::before {
  content: "\e900";
  font-family: "saudi_riyal" !important;
  font-size: inherit;
  color: inherit;
}
 
.icon-saudi_riyal_new::after {
  content: "\20c1";
  font-family: "saudi_riyal" !important;
  font-size: inherit;
  color: inherit;
}

 

        </style>
        @livewireStyles
    </head>
    <body class="antialiased bg-stone-100">
        {{ $slot }}

        @stack('scripts')
        @livewireScripts
    </body>
</html>
