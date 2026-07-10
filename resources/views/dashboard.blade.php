<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Dashboard') }} - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-shape-white.webp') }}">
    {{ Illuminate\Support\Facades\Vite::useHotFile(public_path('dashboard.hot'))->useBuildDirectory('build-dashboard')->withEntryPoints(['dashboard/app.css', 'dashboard/main.js']) }}
</head>
<body class="antialiased bg-stone-100">
    <div id="dashboard-app"></div>
</body>
</html>
