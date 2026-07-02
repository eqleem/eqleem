<x-tenant-theme::module-layout
    icon="hugeicons:presentation-06"
    title="أكاديمية أبعاد البيت"
    desc="دورات عملية في التشطيبات والديكور بأسلوب واضح وتطبيقات واقعية."
    backLink="{{ route('tenant.courses.index') }}"
    backLinkText="العودة لقائمة الدورات"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
