<?php

namespace App\Livewire\Tenant\Pages;

use App\Models\Content;
use App\Support\FaqPageView;
use Livewire\Component;

class Faq extends Component
{
    public Content $page;

    public function mount(): void
    {
        $this->page = Content::query()
            ->type(contentTypeModel('pages'))
            ->template('faq')
            ->published()
            ->where('active', true)
            ->orderBy('id')
            ->firstOrFail();
    }

    public function render()
    {
        return tenantView('page.faq', FaqPageView::make($this->page))
            ->title($this->page->title);
    }
}
