<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeNewsletterTab"
            url-key="section"
            :valid-tabs="['issues', 'customize']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="issues" label="النشرات البريدية" icon="mail" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="customize" label="تخصيص النشرة" icon="settings" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="issues" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.newsletter.table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="customize" class="!p-4">
                    <livewire:admin::page.content.newsletter.customize lazy />
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

    public string $activeNewsletterTab = 'issues';

    /** @var list<string> */
    private const NEWSLETTER_TABS = ['issues', 'customize'];

    public function mount(): void
    {
        $section = request()->query('section', 'issues');

        if (in_array($section, self::NEWSLETTER_TABS, true)) {
            $this->activeNewsletterTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
