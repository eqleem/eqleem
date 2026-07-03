<div>

    <ui:mainbox title="معلومات الصفحة" subtitle="تعديل اسم وشعار الصفحة .">
        <x-slot:icon>
            <ui:icon name="id" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>

        <ui:form wire:submit="submit" id="catalog-info-form">

            <ui:file name="logo" label="الشعار" placeholder="اسم الصفحة" >

                @if ($logo)
                    <img src="{{ $logo->temporaryUrl() }}" class="w-24 rounded mb-1">
                @elseif($currentLogo)
                    <img src="{{ $currentLogo }}?p=logo" class="w-24 rounded mb-1">
                @endif
            </ui:file>

            <ui:input name="name" label="اسم الصفحة" placeholder="اسم الصفحة" />
            {{-- <ui:textarea name="slogan" label="الشعار النصّي" placeholder="مثال: Just do it ✔" /> --}}

            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot>
        </ui:form>


    </ui:mainbox>
 
    {{-- <div class="mt-10">
        <livewire:info.components.contact-info />
    </div> --}}


    {{-- <div class="mt-10">
        <livewire:info.components.social-links />
    </div> --}}
</div>

<?php

use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
// use App\Models\Media;

new class extends \Livewire\Component {
    use WithFileUploads;

    public $tenant;
    public $name;
    // public $slogan;
    public $domain;
    public $logo;
    public $currentLogo;

    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:255',
            // 'slogan' => 'nullable|string|min:2|max:255',
            'logo' => 'nullable|image|max:15024',
        ];
    }

    function mount()
    {
        $this->tenant = currentTenant();
        
        $this->name = $this->tenant->name;
        // $this->slogan = data_get($this->tenant, 'meta.slogan.'.app()->getLocale()) ?? '';
        $this->currentLogo = $this->tenant->logo;
    }
 
    function submit()
    {
        $this->validate();

        $this->tenant->name = $this->name;
        // $this->tenant->meta->set('slogan.' . app()->getLocale(), $this->slogan);
 

        if ($this->logo) {
            // $this->tenant->addMedia($this->logo);
            // $path = $this->logo->storePublicly(path: 'catalog-media/' . $this->tenant->hashId . '/logo', 'spaces');
            $path = $this->logo->storePublicly('tenant-media/' . $this->tenant->uuid . '/logo', 'spaces');

            // $media = new Media();
            // $media->model_type = 'tenant';
            // $media->model_id = $this->tenant->id;
            // $media->collection_name = 'logo';
            // $media->name = $this->logo->getClientOriginalName();
            // $media->file_name = $this->logo->getClientOriginalName();
            // $media->mime_type = $this->logo->getMimeType();
            // $media->file_ext = $this->logo->getClientOriginalExtension();
            // $media->disk = 'spaces';
            // $media->path = $path;
            // $media->save();

            $this->tenant->meta->set('logo', $path);
        }

        $this->tenant->save();

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }
};
?>
