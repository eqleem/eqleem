@php($portfolioSettings = \App\Models\Setting::portfolioSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:folder-library"
    :title="$portfolioSettings['section_title']"
    :desc="$portfolioSettings['section_description']"
    backLink="{{ route('tenant.portfolio.index') }}"
    backLinkText="العودة لملف الأعمال"
>
    {{ $slot }}
</x-tenant-theme::module-layout>