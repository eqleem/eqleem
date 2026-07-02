<x-tenant-theme::module-layout
    icon="hugeicons:home-08"
    title="أبعاد البيت"
    desc="نحوّل أفكارك إلى مساحات أنيقة وعملية."
    backLink="{{ route('tenant.home') }}"
    backLinkText="العودة للرئيسية"
>
    {{ $slot }}
</x-tenant-theme::module-layout>