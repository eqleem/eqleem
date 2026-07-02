<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\RendersBlock;
use App\Models\Block;
use App\Models\Content;
use App\Support\CtaLink;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Cta extends Component
{
    use RendersBlock;

    protected function blockType(): string
    {
        return 'cta';
    }

    public function render(): View
    {
        $type = $this->blockType();
        $tenantId = currentTenantId();

        $block = Block::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereNull('parent_id')
            ->where('type', $type)
            ->first();

        $candidates = array_values(array_filter([
            $block?->variant,
            "tenant-theme::blocks.{$type}",
            "default-tenant-theme::blocks.{$type}",
        ]));

        return view()->first($candidates, [
            'block' => $block,
            'ctaLinks' => $this->preparedLinks($this->ctaLinks($block)),
        ]);
    }

    /**
     * @return Collection<int, Content>
     */
    protected function ctaLinks(?Block $block): Collection
    {
        if (! $block) {
            return collect();
        }

        return Content::query()
            ->where('block_id', $block->id)
            ->type('cta-link')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @param  Collection<int, Content>  $ctaLinks
     * @return Collection<int, array{id: int, label: string, icon: string, url: ?string, isForm: bool, opensInNewTab: bool, formDescription: string}>
     */
    protected function preparedLinks(Collection $ctaLinks): Collection
    {
        $formContentIds = $ctaLinks
            ->filter(fn (Content $link): bool => CtaLink::isForm($link))
            ->map(fn (Content $link): ?int => CtaLink::formContentId($link))
            ->filter()
            ->unique()
            ->values();

        $forms = Content::query()
            ->whereIn('id', $formContentIds)
            ->get()
            ->keyBy('id');

        return $ctaLinks->map(function (Content $link) use ($forms): array {
            $formContentId = CtaLink::formContentId($link);
            $form = $formContentId ? $forms->get($formContentId) : null;

            return [
                'id' => $link->id,
                'label' => CtaLink::label($link),
                'icon' => CtaLink::icon($link),
                'url' => CtaLink::url($link),
                'isForm' => CtaLink::isForm($link),
                'opensInNewTab' => CtaLink::opensInNewTab($link),
                'formDescription' => $form?->data['description'] ?? 'املأ النموذج وسنتواصل معك في أقرب وقت.',
            ];
        });
    }
}
