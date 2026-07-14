<?php

namespace App\Livewire\Tenant\Blocks;

use App\Models\Content;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;
use Livewire\Component;

class PagesMenu extends Component
{
    public function placeholder(): string
    {
        return Blade::render(<<<'HTML'
            <div class="bg-black/10 backdrop-blur-md p-2 px-3 rounded-xl text-black/40 flex items-center gap-x-2 text-base" aria-hidden="true">
                <span class="inline-block size-6 rounded bg-black/10 animate-pulse"></span>
                <span class="hidden md:inline-block h-4 w-14 rounded bg-black/10 animate-pulse"></span>
            </div>
        HTML);
    }

    public function render(): View
    {
        $pages = $this->publishedPages();

        return view('livewire.tenant.blocks.pages-menu', [
            'publishedPages' => $pages,
            'pageMenuIcon' => fn (?string $template): string => TopNav::pageMenuIcon($template),
        ]);
    }

    /**
     * @return Collection<int, Content>
     */
    protected function publishedPages(): Collection
    {
        return Content::query()
            ->type(contentTypeModel('pages'))
            ->published()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'template']);
    }
}
