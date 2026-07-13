<?php

namespace App\Livewire\Tenant\Page;

use App\Models\Block;
use App\Models\Content;
use App\Support\ContactPageView;
use App\Support\FaqPageView;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Detail extends Component
{
    public Content $page;

    public function mount(string $slug): void
    {
        $this->page = Content::query()
            ->type(contentTypeModel('pages'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        $view = match ($this->page->template) {
            'contact' => 'page.contact',
            'faq' => 'page.faq',
            default => 'page.detail',
        };

        $data = match ($this->page->template) {
            'contact' => ContactPageView::make($this->page),
            'faq' => FaqPageView::make($this->page),
            default => [
                'page' => $this->page,
                'subtitle' => (string) data_get($this->page->data, 'subtitle', ''),
                'body' => (string) data_get($this->page->data, 'body', ''),
                'pageBlocks' => $this->pageBlocks(),
            ],
        };

        return tenantView($view, $data)->title($this->page->title);
    }

    /**
     * @return Collection<int, Block>
     */
    protected function pageBlocks(): Collection
    {
        return Block::queryForContent($this->page->id)
            ->userBlocks()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get(['id', 'type', 'variant']);
    }
}
