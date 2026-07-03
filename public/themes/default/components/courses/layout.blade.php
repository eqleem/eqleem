@php($settings = \App\Models\Setting::courseSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:presentation-06"
    :title="$settings['section_title']"
    :desc="$settings['section_description']"
    backLink="{{ route('tenant.courses.index') }}"
    backLinkText="العودة لقائمة الدورات"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
