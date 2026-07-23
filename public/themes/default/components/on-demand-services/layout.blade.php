@php($settings = \App\Models\Setting::onDemandServiceSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:ruler"
    :title="$settings['section_title']"
    :desc="$settings['section_description']"
    backLink="{{ route('tenant.on-demand-services.index') }}"
    backLinkText="العودة لخدمات حسب الطلب"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
