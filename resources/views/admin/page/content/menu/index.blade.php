<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeMenuTab"
            url-key="section"
            :valid-tabs="['items', 'categories', 'customize']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="items" label="الأطباق" icon="chef-hat" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="categories" label="تصنيفات القائمة" icon="category" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="customize" label="تخصيص القائمة" icon="settings" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="items" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.menu.table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="categories" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.menu.categories-table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="customize" class="!p-4">
                    <livewire:admin::page.content.menu.customize lazy />
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

    public string $activeMenuTab = 'items';

    /** @var list<string> */
    private const MENU_TABS = ['items', 'categories', 'customize'];

    public function mount(): void
    {
        $section = request()->query('section', 'items');

        if (in_array($section, self::MENU_TABS, true)) {
            $this->activeMenuTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
