<div>
    <ui:form wire:submit="save" class="!p-4">
        <div class="space-y-2">
            <ui:select name="network" label="الشبكة" :options="$networkOptions" />
            <ui:input name="url" label="الرابط" placeholder="https://..." dir="ltr" />
        </div>

        <x-slot:footer>
            <ui:button type="submit" target="save" label="إضافة" />
        </x-slot:footer>
    </ui:form>
</div>

<?php

use App\Models\Content;
use Illuminate\Support\Str;

new class extends Livewire\Component
{
    public int $headerBlockId;

    public string $network = 'twitter';

    public string $url = '';

    public function mount(int $headerBlockId): void
    {
        $this->headerBlockId = $headerBlockId;
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    protected function networks(): array
    {
        return config('social-networks', []);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'network' => 'required|string|in:'.implode(',', array_keys($this->networks())),
            'url' => 'required|url|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $networks = $this->networks();
        $maxOrder = Content::query()
            ->where('block_id', $this->headerBlockId)
            ->type('social-link')
            ->max('sort_order') ?? 0;

        Content::create([
            'tenant_id' => currentTenantId(),
            'block_id' => $this->headerBlockId,
            'type' => 'social-link',
            'title' => $networks[$this->network]['label'] ?? $this->network,
            'slug' => $this->network.'-'.Str::lower(Str::random(8)),
            'data' => [
                'network' => $this->network,
                'url' => $this->url,
            ],
            'sort_order' => $maxOrder + 1,
            'active' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->reset('url');
        $this->network = 'twitter';

        $this->dispatch('page-completion-updated');
        $this->dispatch('closemodal', modal: 'home-step-social');
        $this->dispatch('notify', text: 'تمت إضافة رابط التواصل بنجاح', type: 'success');
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'networkOptions' => collect($this->networks())
                ->map(fn (array $network): string => $network['label'])
                ->all(),
        ];
    }
}; ?>
