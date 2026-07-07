<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeStoreTab"
            url-key="section"
            :valid-tabs="['products', 'categories', 'customize', 'payment-options', 'shipping-options']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="products" label="المنتجات" icon="shopping-bag" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="categories" label="تصنيفات المتجر" icon="category" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="customize" label="تخصيص المتجر" icon="settings" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="payment-options" :label="config('settings.payment-options.name')" icon="credit-card" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="shipping-options" :label="config('settings.shipping-option.name')" icon="truck-delivery" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="products" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.store.table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="categories" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.store.categories-table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="customize" class="!p-4">
                    <livewire:admin::page.content.store.customize lazy />
                </ui:tab.content>

                <ui:tab.content name="payment-options" class="!p-0 !rounded-none">
                    <livewire:dynamic-component :component="config('settings.payment-options.components.index')" lazy />
                </ui:tab.content>

                <ui:tab.content name="shipping-options" class="!p-0 !rounded-none">
                    <livewire:dynamic-component :component="config('settings.shipping-option.components.index')" lazy />
                </ui:tab.content>
            </x-slot>
        </ui:tab.group>
    </ui:mainbox>
</div>

<?php

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $activeStoreTab = 'products';

    /** @var list<string> */
    private const STORE_TABS = ['products', 'categories', 'customize', 'payment-options', 'shipping-options'];

    public function mount(): void
    {
        $section = request()->query('section', 'products');

        if (in_array($section, self::STORE_TABS, true)) {
            $this->activeStoreTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
