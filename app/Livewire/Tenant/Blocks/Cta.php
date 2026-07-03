<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Models\Block;
use App\Models\Content;
use App\Support\CtaLink;
use App\Support\FormField;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Cta extends Component
{
    use ResolvesTenantBlockView;

    protected function blockType(): string
    {
        return 'cta';
    }

    public function render(): View
    {
        $block = $this->resolveSingletonBlock();

        return $this->renderTenantBlockView($block, [
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

        return $block->activeContents('cta-link');
    }

    /**
     * @param  Collection<int, Content>  $ctaLinks
     * @return Collection<int, array{id: int, label: string, icon: string, url: ?string, isForm: bool, formContentId: ?int, opensInNewTab: bool, formDescription: string, formFields: list<array<string, mixed>>}>
     */
    protected function preparedLinks(Collection $ctaLinks): Collection
    {
        $formContentIds = $ctaLinks
            ->map(fn (Content $link): ?int => CtaLink::formContentId($link))
            ->filter()
            ->unique()
            ->values();

        $forms = Content::query()
            ->type(contentTypeModel('forms'))
            ->whereIn('id', $formContentIds)
            ->where('active', true)
            ->get(['id', 'data'])
            ->keyBy('id');

        return $ctaLinks->map(function (Content $link) use ($forms): array {
            $formContentId = CtaLink::formContentId($link);
            $form = $formContentId ? $forms->get($formContentId) : null;
            $isForm = CtaLink::isForm($link) && $form !== null;

            return [
                'id' => $link->id,
                'label' => CtaLink::label($link),
                'icon' => CtaLink::icon($link),
                'url' => $isForm ? null : CtaLink::url($link),
                'isForm' => $isForm,
                'formContentId' => $isForm ? $formContentId : null,
                'opensInNewTab' => CtaLink::opensInNewTab($link),
                'formDescription' => (string) data_get($form?->data, 'description', 'املأ النموذج وسنتواصل معك في أقرب وقت.'),
                'formFields' => FormField::normalize(data_get($form?->data, 'fields')),
            ];
        });
    }
}
