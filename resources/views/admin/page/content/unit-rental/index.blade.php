<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeUnitRentalTab"
            url-key="section"
            :valid-tabs="['units', 'categories', 'calendars', 'customize']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="units" label="أنواع الوحدات" icon="building-estate" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="categories" label="التصنيفات" icon="category" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="calendars" label="مخزون الوحدات" icon="calendar" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="customize" label="تخصيص القسم" icon="settings" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="units" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.unit-rental.table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="categories" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.unit-rental.categories-table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="calendars" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.unit-rental.calendars-table lazy />
                </ui:tab.content>

                <ui:tab.content name="customize" class="!p-4">
                    <livewire:admin::page.content.unit-rental.customize lazy />
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

    public string $activeUnitRentalTab = 'units';

    /** @var list<string> */
    private const UNIT_RENTAL_TABS = ['units', 'categories', 'calendars', 'customize'];

    public function mount(): void
    {
        $section = request()->query('section', 'units');

        if (in_array($section, self::UNIT_RENTAL_TABS, true)) {
            $this->activeUnitRentalTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
