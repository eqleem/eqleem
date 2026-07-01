<div>
    <ui:mainbox title="تصميم الصفحة" subtitle="تخصيص الألوان والخطوط والمظهر العام للصفحة.">
        <x-slot:icon>
            <img src="{{ asset($tab['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <p class="px-4 lg:px-8 text-sm text-gray-500 leading-relaxed">
            هنا يمكنك تخصيص الألوان والخطوط والمظهر العام للصفحة.
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
