<?php

namespace App\Livewire\Tenant\Pages;

use App\Models\Content;
use App\Support\AboutPageView;
use Livewire\Component;

class About extends Component
{
    public Content $page;

    public function mount(): void
    {
        $this->page = Content::query()
            ->type(contentTypeModel('pages'))
            ->template('about')
            ->published()
            ->where('active', true)
            ->orderBy('id')
            ->firstOrFail();
    }

    public function render()
    {
        return tenantView('page.about', AboutPageView::make($this->page))
            ->title($this->page->title);
    }
}
