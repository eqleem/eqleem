<x-tenant-theme::module-layout
    icon="hugeicons:travel-bag"
    title="خدماتنا"
    desc="خدمات التشطيبات والديكور الداخلي من التصميم للتنفيذ."
    backLink="{{ route('tenant.services.index') }}"
    backLinkText="العودة للخدمات"
>
    {{ $slot }}
</x-tenant-theme::module-layout>