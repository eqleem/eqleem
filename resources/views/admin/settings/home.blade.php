<ui:container>
    <ui:mainbox title="{{ __('Settings') }}" subtitle="تعديل وتخصيص النظام حسب مشروعك.">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                <path opacity=".34" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.5"
                    stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                <path
                    d="M2 12.881v-1.76c0-1.04.85-1.9 1.9-1.9 1.81 0 2.55-1.28 1.64-2.85-.52-.9-.21-2.07.7-2.59l1.73-.99c.79-.47 1.81-.19 2.28.6l.11.19c.9 1.57 2.38 1.57 3.29 0l.11-.19c.47-.79 1.49-1.07 2.28-.6l1.73.99c.91.52 1.22 1.69.7 2.59-.91 1.57-.17 2.85 1.64 2.85 1.04 0 1.9.85 1.9 1.9v1.76c0 1.04-.85 1.9-1.9 1.9-1.81 0-2.55 1.28-1.64 2.85.52.91.21 2.07-.7 2.59l-1.73.99c-.79.47-1.81.19-2.28-.6l-.11-.19c-.9-1.57-2.38-1.57-3.29 0l-.11.19c-.47.79-1.49 1.07-2.28.6l-1.73-.99a1.899 1.899 0 0 1-.7-2.59c.91-1.57.17-2.85-1.64-2.85-1.05 0-1.9-.86-1.9-1.9Z"
                    stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                    stroke-linejoin="round"></path>
            </svg>
        </x-slot>
        <div class="p-4 lg:p-6 px-4 lg:px-8 grid lg:grid-cols-4 gap-5 grid-cols-2 border-t-2 border-dotted">
            @foreach ($settings as  $setting)
                
                    <a href="{{ route('admin.settings.detail', ['slug' => $setting['slug']]) }}" wire:navigate
                        class="flex gap-x-3 items-center bg-gray-100 hover:bg-gray-200/80 p-2 rounded-xl">
                        <div class="bg-white p-2 rounded-xl shrink-0">
                            <img class="h-10 w-10" src="{{ asset($setting['icon']) }}"
                                alt="{{ __($setting['name']) }}">
                        </div>
                        <div class="truncate">
                            <p class="text-sm font-medium text-gray-700">
                                {{ __($setting['name']) }}
                            </p>
                            <small class="text-gray-500 font-normal text-xs truncate">
                                {{ $setting['description'] }} </small>
                        </div>
                    </a>
              
            @endforeach
        </div>
    </ui:mainbox>
</ui:container>

 <?php
 
 new class extends \Livewire\Component {

    public $settings = [];

    public function mount(): void
    {
        $this->settings = config('settings');
    }

     public function render()
     {
         return $this->view()->layout('admin::layout')->title(__('Settings'));
     }
 }; ?>
