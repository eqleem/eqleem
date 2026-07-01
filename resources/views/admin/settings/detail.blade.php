<ui:container title="{{ data_get($setting, 'name') }}" backRoute="{{ route('admin.settings.home') }}">
    
        @livewire(data_get($setting, 'components.index'), ['setting' => $setting])
    
</ui:container>

 <?php

 new class extends \Livewire\Component {

    public $slug;
    public $setting;

    public function mount(): void
    {
        $this->slug = request()->slug;
        $this->setting = data_get(config('settings'), $this->slug);
    }

    public function render()
    {
        return $this->view()->layout('admin::layout')->title(__('Settings'));
    }
 }; ?>