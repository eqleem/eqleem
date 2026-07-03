<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeDigitalServicesTab"
            url-key="section"
            :valid-tabs="['services', 'categories', 'customize']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="services" label="الخدمات" icon="cloud-computing" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="categories" label="التصنيفات" icon="category" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="customize" label="تخصيص القسم" icon="settings" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="services" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.digital-services.table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="categories" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.digital-services.categories-table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="customize" class="!p-4">
                    <livewire:admin::page.content.digital-services.customize lazy />
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

    public string $activeDigitalServicesTab = 'services';

    /** @var list<string> */
    private const DIGITAL_SERVICE_TABS = ['services', 'categories', 'customize'];

    public function mount(): void
    {
        $section = request()->query('section', 'services');

        if (in_array($section, self::DIGITAL_SERVICE_TABS, true)) {
            $this->activeDigitalServicesTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
