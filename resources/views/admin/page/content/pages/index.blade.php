<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activePagesTab"
            url-key="section"
            :valid-tabs="['pages']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="pages" label="الصفحات" icon="article" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="pages" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.pages.table :contentType="$contentType" lazy />
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

    public string $activePagesTab = 'pages';

    /** @var list<string> */
    private const PAGES_TABS = ['pages'];

    public function mount(): void
    {
        $section = request()->query('section', 'pages');

        if (in_array($section, self::PAGES_TABS, true)) {
            $this->activePagesTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
