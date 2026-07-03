@php($settings = \App\Models\Setting::digitalProductSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:file-download"
    :title="$settings['section_title']"
    :desc="$settings['section_description']"
    backLink="{{ route('tenant.digital-products.index') }}"
    backLinkText="العودة للمنتجات الرقمية"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
