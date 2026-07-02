<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Models\Block;
use App\Models\Content;
use App\Support\BusinessDocuments;
use App\Support\CtaLink;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Footer extends Component
{
    use ResolvesTenantBlockView;

    protected function blockType(): string
    {
        return 'footer';
    }

    public function render(): View
    {
        $block = $this->resolveSingletonBlock();
        $blockData = $block?->data ?? [];

        return $this->renderTenantBlockView($block, [
            'showDocumentsWarranties' => BusinessDocuments::showsDocumentsWarranties($blockData),
            'businessDocuments' => BusinessDocuments::visibleForBlockData($blockData),
            'footerLinks' => $this->preparedLinks($this->footerLinks($block)),
        ]);
    }

    /**
     * @return Collection<int, Content>
     */
    protected function footerLinks(?Block $block): Collection
    {
        if (! $block) {
            return collect();
        }

        return $block->activeContents('footer-link');
    }

    /**
     * @param  Collection<int, Content>  $footerLinks
     * @return Collection<int, array{id: int, label: string, url: ?string, opensInNewTab: bool}>
     */
    protected function preparedLinks(Collection $footerLinks): Collection
    {
        return $footerLinks->map(fn (Content $link): array => [
            'id' => $link->id,
            'label' => CtaLink::label($link),
            'url' => CtaLink::url($link),
            'opensInNewTab' => CtaLink::opensInNewTab($link),
        ]);
    }
}
