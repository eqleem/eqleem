<x-tenant::module-layout
    icon="hugeicons:mail-at-sign-02"
    title="النشرة البريدية"
    desc="أحدث مقالات النشرة الأسبوعية ونشراتنا المتخصصة."
    backLink="{{ route('tenant.newsletter.index') }}"
    backLinkText="العودة للنشرة البريدية"
>
    {{ $slot }}
</x-tenant::module-layout>
