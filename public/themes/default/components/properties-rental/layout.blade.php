@php($settings = \App\Models\Setting::unitRentalSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:bed-double"
    :title="$settings['section_title']"
    :desc="$settings['section_description']"
    backLink="{{ route('tenant.properties-rental.index') }}"
    backLinkText="العودة لقسم تأجير الوحدات"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
