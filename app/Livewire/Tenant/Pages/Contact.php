<?php

namespace App\Livewire\Tenant\Pages;

use App\Models\Content;
use App\Support\ContactPageView;
use Livewire\Component;

class Contact extends Component
{
    public Content $page;

    public function mount(): void
    {
        $this->page = Content::query()
            ->type(contentTypeModel('pages'))
            ->template('contact')
            ->published()
            ->where('active', true)
            ->orderBy('id')
            ->firstOrFail();
    }

    public function render()
    {
        return tenantView('page.contact', ContactPageView::make($this->page))
            ->title($this->page->title);
    }
}
