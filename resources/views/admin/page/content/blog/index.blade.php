<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:tab.group
            :active="$activeBlogTab"
            url-key="section"
            :valid-tabs="['posts', 'categories', 'customize']"
        >
            <x-slot name="nav" class="border-b border-stone-200 px-px">
                <ui:tab.nav name="posts" label="التدوينات" icon="article" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="categories" label="تصنيفات المدونة" icon="category" activeClass="border-b-2 !border-primary-500 text-stone-900" />
                <ui:tab.nav name="customize" label="تخصيص المدونة" icon="palette" activeClass="border-b-2 !border-primary-500 text-stone-900" />
            </x-slot>

            <x-slot name="content">
                <ui:tab.content name="posts" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.blog.table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="categories" class="!p-0 !rounded-none">
                    <livewire:admin::page.content.blog.categories-table :contentType="$contentType" lazy />
                </ui:tab.content>

                <ui:tab.content name="customize">
                    <p class="text-sm text-stone-600 leading-relaxed">
                        هذا نص تجريبي لتبويب تخصيص المدونة. سيتم تعديله لاحقاً.
                    </p>
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

    public string $activeBlogTab = 'posts';

    /** @var list<string> */
    private const BLOG_TABS = ['posts', 'categories', 'customize'];

    public function mount(): void
    {
        $section = request()->query('section', 'posts');

        if (in_array($section, self::BLOG_TABS, true)) {
            $this->activeBlogTab = $section;
        }
    }

    public function render()
    {
        return $this->view();
    }
}; ?>
