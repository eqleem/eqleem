@php($storeSettings = \App\Models\Setting::storeSettings())

<x-tenant-theme::module-layout
    icon="hugeicons:store-02"
    :title="$storeSettings['section_title']"
    :desc="$storeSettings['section_description']"
    backLink="{{ route('tenant.store.index') }}"
    backLinkText="العودة للمتجر"
>
    <x-slot:actions>
        <livewire:tenant.cart.badge />
    </x-slot:actions>

    {{ $slot }}
</x-tenant-theme::module-layout>
