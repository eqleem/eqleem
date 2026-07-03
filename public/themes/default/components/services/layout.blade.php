@php($settings = \App\Models\Setting::serviceSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:travel-bag"
    :title="$settings['section_title']"
    :desc="$settings['section_description']"
    backLink="{{ route('tenant.services.index') }}"
    backLinkText="العودة للخدمات"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
