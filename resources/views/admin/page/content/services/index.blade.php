<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeServicesTab"
            url-key="section"
            :valid-tabs="['services', 'categories', 'calendars', 'customize']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="services" label="الخدمات" icon="hotel-service" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="categories" label="التصنيفات" icon="category" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="calendars" label="مقدمو الخدمات" icon="calendar" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="customize" label="تخصيص القسم" icon="settings" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="services" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.services.table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="categories" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.services.categories-table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="calendars" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.services.calendars-table lazy />
                </ui:tab.content>

                <ui:tab.content name="customize" class="!p-4">
                    <livewire:admin::page.content.services.customize lazy />
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

    public string $activeServicesTab = 'services';

    /** @var list<string> */
    private const SERVICES_TABS = ['services', 'categories', 'calendars', 'customize'];

    public function mount(): void
    {
        $section = request()->query('section', 'services');

        if (in_array($section, self::SERVICES_TABS, true)) {
            $this->activeServicesTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
