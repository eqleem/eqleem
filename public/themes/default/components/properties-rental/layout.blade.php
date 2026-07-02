<x-tenant-theme::module-layout
    icon="hugeicons:bed-double"
    title="تأجير الوحدات"
    desc="وحدات سكنية جاهزة للحجز اليومي بتفاصيل واضحة وأسعار مباشرة."
    :back-link="route('tenant.properties-rental.index')"
    back-link-text="العودة لقسم تأجير الوحدات"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
