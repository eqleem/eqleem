@php($settings = \App\Models\Setting::menuSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:restaurant-01"
    :title="$settings['section_title']"
    :desc="$settings['section_description']"
    backLink="{{ route('tenant.menu.index') }}"
    backLinkText="العودة للمنيو"
>
 

    {{ $slot }}
</x-tenant-theme::module-layout>
