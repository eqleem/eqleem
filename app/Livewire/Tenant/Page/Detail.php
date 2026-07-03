<?php

namespace App\Livewire\Tenant\Page;

use App\Models\Block;
use App\Models\Content;
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
        return tenantView('page.detail', [
            'page' => $this->page,
            'subtitle' => (string) data_get($this->page->data, 'subtitle', ''),
            'body' => (string) data_get($this->page->data, 'body', ''),
            'pageBlocks' => $this->pageBlocks(),
        ])->title($this->page->title);
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
