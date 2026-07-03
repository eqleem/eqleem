<div>
    <ui:form wire:submit="save" class="!p-4">
        <div class="space-y-2">
            <ui:input name="name" label="اسم الصفحة" placeholder="اسم الصفحة" />

            <ui:toggle name="showAvatar" label="عرض الشعار" live />

            @if ($showAvatar)
                <ui:file name="logo" label="الشعار" uploadLabel="رفع شعار">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="mb-1 size-20 rounded-full object-cover">
                    @elseif ($currentLogo)
                        <img src="{{ $currentLogo }}" class="mb-1 size-20 rounded-full object-cover">
                    @endif
                </ui:file>
            @endif

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

    public bool $showAvatar = true;

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
        $this->showAvatar = (bool) ($data['show_avatar'] ?? true);
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
                    'show_avatar' => $this->showAvatar,
                    'bio' => $this->bio,
                ]),
            ]);
        }

        $this->dispatch('page-completion-updated');
        $this->dispatch('closemodal', modal: 'home-step-basic-info');
        $this->dispatch('notify', text: __('Settings updated successfully.'), type: 'success');
    }
}; ?>
