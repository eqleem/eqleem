<ui:form wire:submit="submit">
    <ui:input name="title" label="عنوان النشرة" placeholder="اكتب عنوان النشرة البريدية" />

    <x-slot:footer>
        <ui:button target="submit" label="{{ __('Save') }}" />
    </x-slot>
</ui:form>

<?php

use App\Models\Content;
use Illuminate\Support\Str;

new class extends Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $title = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|min:1|max:255',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $tenantId = currentTenantId();

        if (! $tenantId) {
            $this->addError('title', __('No tenant selected.'));

            return;
        }

        $slug = $this->uniqueSlug(Str::slug($this->title));

        $content = Content::query()->create([
            'tenant_id' => $tenantId,
            'type' => contentTypeModel($this->contentType['slug']),
            'title' => $this->title,
            'slug' => $slug,
            'status' => 'draft',
            'active' => true,
            'data' => [
                'mail_status' => 'draft',
            ],
        ]);

        $this->dispatch('updateNewsletterList');
        $this->dispatch('closemodal', modal: 'add-newsletter');
        $this->dispatch(
            'openContentItem',
            tab: $this->contentType['tab_id'],
            item: $content->uuid,
        );
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'newsletter';
        $counter = 1;

        while (Content::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}; ?>
