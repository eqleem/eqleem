@php($blogSettings = \App\Models\Setting::blogSettings())

<x-tenant::module-layout
    icon="hugeicons:book-open-text"
    :title="$blogSettings['section_title']"
    :desc="$blogSettings['section_description']"
    backLink="{{ route('tenant.blog.index') }}"
    backLinkText="العودة للمدونة"
>
    {{ $slot }}
</x-tenant::module-layout>