<x-tenant::module-layout
    icon="hugeicons:building-03"
    title="العقارات"
    desc="عروض تأجير وبيع عقارية محدثة مع تفاصيل واضحة وتواصل مباشر مع المسوق."
    :back-link="route('tenant.properties.index')"
    back-link-text="العودة لقسم العقارات"
>
    {{ $slot }}
</x-tenant::module-layout>
