<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeCoursesTab"
            url-key="section"
            :valid-tabs="['courses', 'categories', 'customize']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="courses" label="الدورات" icon="school-bell" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="categories" label="التصنيفات" icon="category" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="customize" label="تخصيص القسم" icon="settings" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="courses" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.courses.table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="categories" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.courses.categories-table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="customize" class="!p-4">
                    <livewire:admin::page.content.courses.customize lazy />
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

    public string $activeCoursesTab = 'courses';

    /** @var list<string> */
    private const COURSE_TABS = ['courses', 'categories', 'customize'];

    public function mount(): void
    {
        $section = request()->query('section', 'courses');

        if (in_array($section, self::COURSE_TABS, true)) {
            $this->activeCoursesTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
