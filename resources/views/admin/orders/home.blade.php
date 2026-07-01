<ui:container>
    <ui:mainbox title="{{ __('Orders') }}"
        subtitle="{{ __('الطلبات والمشتريات، تجدها هنا.') }}">
        <x-slot:icon>
            <ui:icon name="message-2" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>
        <x-slot:actions>
            <ui:button @click.prevent="$dispatch('openmodal', { modal: 'add-order' })" label="إضافة طلب"
                icon="square-rounded-plus" />
        </x-slot:actions>

        <livewire:admin::orders.table lazy />

        <ui:modal title="إضافة طلب جديد" size="4xl" name="add-order">
            <livewire:admin::orders.add-order />
        </ui:modal>
    </ui:mainbox>
</ui:container>

<?php

new class extends \Livewire\Component {
    public function rendering($view): void
    {
        $view->title(__('Orders'))->layout('admin::layout');
    }
}; ?>
