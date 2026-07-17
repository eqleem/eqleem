<x-tenant-theme::module-layout
    icon="hugeicons:home-08"
    :title="tenant('name')"
    :desc="tenant('bio')"
    backLink="{{ route('tenant.home') }}"
    backLinkText="العودة للرئيسية"
>
    {{ $slot }}
</x-tenant-theme::module-layout>