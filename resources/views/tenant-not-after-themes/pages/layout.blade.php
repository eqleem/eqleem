<x-tenant::module-layout
    icon="hugeicons:home-08"
    title="{{ tenant('name') }}"
    desc="{{ tenant('description') }}"
    backLink="{{ route('tenant.home') }}"
    backLinkText="العودة للرئيسية"
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
