<x-tenant-theme::module-layout
    icon="hugeicons:playlist-01"
    title="قوائم التشغيل"
    :desc="$desc ?? 'استعرض القوائم ثم ادخل للتفاصيل والتشغيل الكامل.'"
    backLink="{{ route('tenant.playlists.index') }}"
    backLinkText="العودة لقوائم التشغيل"
>
    {{ $slot }}
</x-tenant-theme::module-layout>