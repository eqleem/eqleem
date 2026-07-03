@php($settings = \App\Models\Setting::newsletterSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:mail-at-sign-02"
    :title="$settings['section_title']"
    :desc="$settings['section_description']"
    backLink="{{ route('tenant.newsletter.index') }}"
    backLinkText="العودة للنشرة البريدية"
>
    {{ $slot }}
</x-tenant-theme::module-layout>
