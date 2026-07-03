@php($settings = \App\Models\Setting::digitalServiceSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:customer-service-01"
    :title="$settings['section_title']"
    :desc="$settings['section_description']"
    backLink="{{ route('tenant.digital-services.index') }}"
    backLinkText="العودة للخدمات الرقمية"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
