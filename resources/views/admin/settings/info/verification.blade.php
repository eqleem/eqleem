<div class="grid gap-y-6">

    <ui:mainbox title="توثيق المتجر"
        subtitle="بيانات توثيق المتجر بالمستندات الرسمية.">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10"></path>
                <g opacity=".4">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 3h1a28.424 28.424 0 000 18H8M15 3c.97 2.92 1.46 5.96 1.46 9"></path>
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 16v-1c2.92.97 5.96 1.46 9 1.46M3 9a28.424 28.424 0 0118 0"></path>
                </g>
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M18.2 21.4a3.2 3.2 0 100-6.4 3.2 3.2 0 000 6.4zM22 22l-1-1"></path>
            </svg>
        </x-slot:icon>

        @if (!$is_confirmed && $confirm_status != 'pending')
        <div class="px-4 mt-3">
            <ui:alert type="info" title="توثيق المتجر" text="يجب عليك توثيق المتجر لتتمكن من استقبال المدفوعات من عملائك." />
        </div>
         @endif

        @if ($is_confirmed)
            <div class="px-4 mt-3">
                <ui:alert type="success" color="green" title="توثيق المتجر" text="تم توثيق المتجر بنجاح." />
            </div>
        @endif

        @if ($confirm_status == 'pending')
        <div class="px-4 mt-3">
            <ui:alert type="info" color="yellow" title="توثيق المتجر" text="تم إرسال طلب توثيق المتجر، سيتم توثيقه خلال يوم عمل على الأكثر." />
        </div>
        @endif
        <ui:form wire:submit="submit">
            <ui:radio name="identity_type" :live="true" label="نوع الهوية" :options="$types" />
            <ui:input name="identity_number" label="{{$identity_type == 'individual' ? 'رقم الهوية' : 'رقم السجل التجاري'}}"  placeholder="1234567890" inputWidth="w-lg" />
            <ui:select name="country" label="الدولة" :options="config('verification.countries')" />
            <ui:file name="file" label="ملف الهوية" info="الرجاء إرفاق صورة الهوية أو صورة السجل التجاري حسب نوع الكيان التجاري الذي تستخدمه.">
                <div class="flex flex-col gap-2">
                    @if ($current_file)
                            <div class="text-sm text-gray-500 bg-white hover:bg-primary-50 p-2 rounded-md truncate">
                                <x-ui::icon name="download" />
                                <a href="{{ \Storage::url($current_file) }}" target="_blank">{{ \Str::of($current_file)->after('identity/') }}</a>
                            </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <input type="file" wire:model="file" />
                    </div>
                </div>
            </ui:file>
            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot>
        </ui:form>
    </ui:mainbox>
 
</div>

<?php
use App\Models\Tenant;
use Livewire\WithFileUploads;

new class extends Livewire\Component {
    use WithFileUploads;

    public $tenant;
 
    public $identity_type ;
    public $identity_number;
    public $country = 'SA';
    public $types = [];
    public $file;
    public $current_file;
    public $is_confirmed;
    public $confirm_status;

    function mount()
    {
        $this->tenant = tenant();
          
        $this->identity_type = data_get($this->tenant->meta, 'identity_type') ?: 'individual';
        $this->identity_number = data_get($this->tenant->meta, 'identity_number') ;
        $this->country = data_get($this->tenant->meta, 'country') ?: 'SA';
        $this->current_file = data_get($this->tenant->meta, 'identity_file') ;
        $this->is_confirmed =  (boolean) data_get($this->tenant->meta, 'is_confirmed');
        $this->confirm_status = data_get($this->tenant->meta, 'confirm_status') ;

        $this->types = [
            'individual' => __('Individual'),
            'llc' => __('LLC'),
            'company' => __('Company'),
            'charity' => __('Charity'),
        ];
    }

    function submit()
    {
        if($this->current_file) {
            $this->validate([
                'identity_type' => 'required',
                'identity_number' => 'required|min:8|max:255',
            ]);
        } else {
            $this->validate([
                'identity_type' => 'required',
                'identity_number' => 'required|min:8|max:255',
                'file' => 'required|image|max:5024',
            ]);
        }

        $this->tenant->meta->set('identity_type', $this->identity_type);
        $this->tenant->meta->set('identity_number', $this->identity_number);
        $this->tenant->meta->set('country', $this->country);

        if ($this->file) {
            $path = $this->file->storePublicly('catalog-media/' . $this->tenant->hashId . '/identity', 'spaces');
            $this->tenant->meta->set('identity_file', $path);
        }
 
        $this->tenant->meta->set('is_confirmed', false); // always false when uploading the identity file
        $this->tenant->meta->set('confirm_status', 'pending');
        $this->tenant->save();
    

        $this->dispatch('notify', text: __('Identity updated successfully.'));
        $this->dispatch('page-completion-updated');
        $this->dispatch('closemodal', modal: 'home-step-verification');
        $this->mount();
  
    }
 
}; ?>
