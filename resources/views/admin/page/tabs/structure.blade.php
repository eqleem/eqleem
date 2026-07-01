<div>
    <ui:mainbox title="هيكل الصفحة" subtitle="إدارة ترتيب الأقسام وإضافة أو إزالة الكتل من الصفحة الرئيسية.">
        <x-slot:icon>
            <img src="{{ asset($tab['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <p class="px-4 lg:px-8 text-sm text-gray-500 leading-relaxed">
            هنا يمكنك إدارة ترتيب الأقسام وإضافة أو إزالة الكتل من الصفحة الرئيسية.
        </p>
    </ui:mainbox>
</div>

<?php

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $tab = [];

    public function render()
    {
        return $this->view();
    }
}; ?>
