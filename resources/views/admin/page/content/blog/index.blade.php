<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>
        <x-slot:actions>
            <ui:button @click.prevent="$dispatch('openmodal', { modal: 'add-blog-post' })" label="تدوينة جديدة"
                icon="square-rounded-plus" />
        </x-slot:actions>

        <livewire:admin::page.content.blog.table :contentType="$contentType" lazy />

        <ui:modal title="إضافة تدوينة جديدة" size="2xl" name="add-blog-post">
            <livewire:admin::page.content.blog.add-post :contentType="$contentType" />
        </ui:modal>
    </ui:mainbox>
</div>

<?php

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public function render()
    {
        return $this->view();
    }
}; ?>
