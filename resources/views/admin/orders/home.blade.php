<ui:container>
    <ui:mainbox title="{{ __('Orders') }}"
        subtitle="{{ __('الطلبات والمشتريات، تجدها هنا.') }}">
        <x-slot:icon>
            <ui:icon name="message-2" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>
        <x-slot:actions>
            @if ($activeOrdersTab === 'orders')
                <ui:button @click.prevent="$dispatch('openmodal', { modal: 'add-order' })" label="إضافة طلب"
                    icon="square-rounded-plus" />
            @endif
        </x-slot:actions>

        <ui:tab.group
            :active="$activeOrdersTab"
            url-key="tab"
            :valid-tabs="['orders', 'form-submissions']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="orders" label="الطلبات" icon="message-2" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav
                    name="form-submissions"
                    label="ردود النماذج"
                    icon="clipboard-list"
                    :badge="$unreadFormSubmissionsCount > 0 ? $unreadFormSubmissionsCount : null"
                    activeClass="border-b-2 !border-primary-500 text-stone-900"
                />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="orders" class="!p-0 !rounded-none">
                    <livewire:admin::orders.table lazy />

                    <ui:modal title="إضافة طلب جديد" size="4xl" name="add-order" :escape="false">
                        <livewire:admin::orders.add-order />
                    </ui:modal>
                </ui:tab.content>

                <ui:tab.content name="form-submissions" class="!p-0 !rounded-none">
                    <livewire:admin::orders.form-submissions-table lazy />
                </ui:tab.content>
            </x-slot>
        </ui:tab.group>
    </ui:mainbox>
</ui:container>

<?php

use App\Models\FormSubmission;

new class extends \Livewire\Component {
    public string $activeOrdersTab = 'orders';

    /** @var list<string> */
    private const ORDERS_TABS = ['orders', 'form-submissions'];

    public function mount(): void
    {
        $tab = request()->query('tab', 'orders');

        if (in_array($tab, self::ORDERS_TABS, true)) {
            $this->activeOrdersTab = $tab;
        }
    }

    public function with(): array
    {
        return [
            'unreadFormSubmissionsCount' => FormSubmission::unreadCount(currentTenantId()),
        ];
    }

    public function rendering($view): void
    {
        $view->title(__('Orders'))->layout('admin::layout');
    }
}; ?>
