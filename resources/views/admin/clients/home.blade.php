<ui:container>
    <ui:mainbox title="{{ __('Clients') }}"
        subtitle="{{ __('العملاء والزبائن، تجدها هنا.') }}">
        <x-slot:icon>
            <ui:icon name="message-2" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>
        <x-slot:actions>
            <ui:button @click.prevent="$dispatch('openmodal', { modal: 'add-client' })" label="{{ __('Add client') }}"
                icon="square-rounded-plus" />
        </x-slot:actions>

        <livewire:admin::clients.table lazy />

        <ui:modal title="{{ __('Add new client') }}" size="2xl" name="add-client">
            <livewire:admin::clients.add-client />
        </ui:modal>
    </ui:mainbox>
</ui:container>

<?php

new class extends \Livewire\Component {
    public function rendering($view): void
    {
        $view->title(__('Clients'))->layout('admin::layout');
    }
}; ?>
