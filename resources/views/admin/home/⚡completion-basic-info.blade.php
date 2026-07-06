<div>
    <ui:form wire:submit="save" class="!p-4">
        <div class="space-y-2">
            <ui:input name="name" label="اسم الصفحة" placeholder="اسم الصفحة" />

            <ui:file-crop
                name="logo"
                label="الشعار"
                uploadLabel="رفع شعار"
                shape="square"
                cropTitle="قص الشعار"
                previewClass="mb-1 size-20 rounded-lg object-cover"
                :preview="$logo ?: ($currentLogo ?: null)"
            />

            <ui:textarea
                name="bio"
                label="النبذة"
                placeholder="نبذة قصيرة تظهر أسفل الاسم"
                maxlength="250"
                rows="3"
            />
        </div>

        <x-slot:footer>
            <ui:button type="submit" target="save" label="{{ __('Save') }}" />
        </x-slot:footer>
    </ui:form>
</div>

<?php

use App\Models\Block;
use Livewire\WithFileUploads;

new class extends Livewire\Component
{
    use WithFileUploads;

    public int $headerBlockId;

    public string $name = '';

    public $logo = null;

    public string $currentLogo = '';

    public string $bio = '';

    public function mount(int $headerBlockId): void
    {
        $this->headerBlockId = $headerBlockId;

        $tenant = currentTenant();
        $headerBlock = Block::queryForTenantRoots()->find($headerBlockId);
        $data = $headerBlock?->data ?? [];

        $this->name = (string) ($tenant?->name ?? '');
        $this->currentLogo = (string) ($tenant?->logo ?? '');
        $this->bio = (string) ($data['bio'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'bio' => 'nullable|string|max:250',
            'logo' => 'nullable|image|max:15024',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $tenant = currentTenant();
        $headerBlock = Block::queryForTenantRoots()->find($this->headerBlockId);

        if ($tenant) {
            $tenant->name = $this->name;

            if ($this->logo) {
                $path = $this->logo->storePublicly('tenant-media/'.$tenant->uuid.'/logo', 'spaces');
                $tenant->meta->set('logo', $path);
            }

            $tenant->save();

            $this->currentLogo = $tenant->logo;
            $this->reset('logo');
        }

        if ($headerBlock) {
            $headerBlock->update([
                'data' => array_merge($headerBlock->data ?? [], [
                    'bio' => $this->bio,
                ]),
            ]);
        }

        $this->dispatch('page-completion-updated');
        $this->dispatch('closemodal', modal: 'home-step-basic-info');
        $this->dispatch('notify', text: __('Settings updated successfully.'), type: 'success');
    }
}; ?>
