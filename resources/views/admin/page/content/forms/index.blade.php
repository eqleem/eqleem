<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeFormsTab"
            url-key="section"
            :valid-tabs="['forms']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="forms" label="النماذج" icon="clipboard-list" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="forms" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.forms.table :contentType="$contentType" lazy />
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

    public string $activeFormsTab = 'forms';

    /** @var list<string> */
    private const FORMS_TABS = ['forms'];

    public function mount(): void
    {
        $section = request()->query('section', 'forms');

        if (in_array($section, self::FORMS_TABS, true)) {
            $this->activeFormsTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
