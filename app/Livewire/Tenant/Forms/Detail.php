<?php

namespace App\Livewire\Tenant\Forms;

use App\Models\Content;
use App\Support\FormField;
use Livewire\Component;

class Detail extends Component
{
    public Content $form;

    public function mount(string $slug): void
    {
        $this->form = Content::query()
            ->type(contentTypeModel('forms'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        $description = (string) data_get($this->form->data, 'description', '');
        $fields = FormField::normalize(data_get($this->form->data, 'fields'));

        return tenantView('forms.detail', [
            'form' => $this->form,
            'description' => $description,
            'fields' => $fields,
        ])
            ->title($this->form->title)
            ->layoutData([
                'metaDescription' => filled($description) ? $description : $this->form->title,
            ]);
    }
}
