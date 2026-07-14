<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Models\Block;
use App\Models\Content;
use App\Support\BlockBrandMark;
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
     * @return Collection<int, array{id: int, label: string, icon: string, brand_mark: array{type: string, value: string, color: string, url: string|null}|null, url: ?string, isForm: bool, formContentId: ?int, opensInNewTab: bool, formDescription: string, formFields: list<array<string, mixed>>}>
     */
    protected function preparedLinks(Collection $ctaLinks): Collection
    {
        $formContentIds = $ctaLinks
            ->map(fn (Content $link): ?int => CtaLink::formContentId($link))
            ->filter()
            ->unique()
            ->values();

        $forms = $formContentIds->isEmpty()
            ? collect()
            : Content::query()
                ->type(contentTypeModel('forms'))
                ->whereIn('id', $formContentIds)
                ->where('active', true)
                ->get(['id', 'data'])
                ->keyBy('id');

        return $ctaLinks->map(function (Content $link) use ($forms): array {
            $formContentId = CtaLink::formContentId($link);
            $form = $formContentId ? $forms->get($formContentId) : null;
            $isForm = CtaLink::isForm($link) && $form !== null;
            $data = is_array($link->data) ? $link->data : [];
            $formFields = $isForm ? FormField::normalize(data_get($form?->data, 'fields')) : [];

            return [
                'id' => $link->id,
                'label' => CtaLink::label($link),
                'icon' => CtaLink::icon($link),
                'brand_mark' => BlockBrandMark::forDisplay(
                    is_array($data['brand_mark'] ?? null) ? $data['brand_mark'] : null
                ),
                'url' => $isForm ? null : CtaLink::url($link),
                'isForm' => $isForm,
                'formContentId' => $isForm ? $formContentId : null,
                'opensInNewTab' => CtaLink::opensInNewTab($link),
                'formDescription' => $isForm
                    ? (string) data_get($form?->data, 'description', 'املأ النموذج وسنتواصل معك في أقرب وقت.')
                    : '',
                'formFields' => $formFields,
            ];
        });
    }
}
