<x-tenant::module-layout
    icon="hugeicons:travel-bag"
    title="خدماتنا"
    desc="خدمات التشطيبات والديكور الداخلي من التصميم للتنفيذ."
    backLink="{{ route('tenant.services.index') }}"
    backLinkText="العودة للخدمات"
>
    {{ $slot }}
</x-tenant::module-layout>

<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
