<?php

namespace App\Livewire\Concerns;

use App\Models\Content;
use App\Support\CtaLink;
use Illuminate\Support\Collection;

trait ManagesCtaLinkFields
{
    public string $linkType = '';

    public ?int $contentId = null;

    public string $contentSearch = '';

    public string $selectedContentTitle = '';

    public bool $showContentResults = false;

    public string $url = '';

    public string $icon = 'hugeicons:link-04';

    abstract protected function ctaLinkProfile(): string;

    protected function ctaLinkDefaultType(): string
    {
        return CtaLink::defaultTypeKey($this->ctaLinkProfile());
    }

    /**
     * @return array<string, string>
     */
    protected function ctaLinkTypeOptions(): array
    {
        return CtaLink::linkTypeOptions($this->ctaLinkProfile());
    }

    /**
     * @return list<string>
     */
    protected function ctaLinkAllowedTypeKeys(): array
    {
        if ($this->ctaLinkProfile() === 'block') {
            return CtaLink::allowedBlockLinkTypeKeys();
        }

        return array_keys($this->ctaLinkTypeOptions());
    }

    public function isExternalLink(): bool
    {
        return CtaLink::isExternalLink($this->linkType);
    }

    public function needsContentPicker(): bool
    {
        return CtaLink::needsContentPicker($this->linkType);
    }

    public function linkNamePlaceholder(): string
    {
        return CtaLink::linkNamePlaceholder($this->linkType, $this->ctaLinkProfile());
    }

    public function linkNameHint(): string
    {
        return CtaLink::linkNameHint($this->linkType, $this->ctaLinkProfile());
    }

    public function contentPickerLabel(): string
    {
        return CtaLink::contentPickerLabel($this->linkType);
    }

    public function showRecentContent(): void
    {
        if (mb_strlen(trim($this->contentSearch)) < 2) {
            $this->showContentResults = true;
        }
    }

    public function updatedContentSearch(): void
    {
        $this->contentId = null;
        $this->selectedContentTitle = '';

        $searchLength = mb_strlen(trim($this->contentSearch));

        $this->showContentResults = match (true) {
            $searchLength >= 2 => true,
            $searchLength === 0 => $this->showContentResults,
            default => false,
        };

        $this->resetErrorBag('contentId');
    }

    public function selectContent(int $id): void
    {
        $content = $this->findPickableContent($id);

        if (! $content) {
            return;
        }

        $this->contentId = $content->id;
        $this->selectedContentTitle = $content->title;
        $this->contentSearch = $content->title;
        $this->showContentResults = false;
        $this->resetErrorBag('contentId');
    }

    public function clearContentSelection(): void
    {
        $this->contentId = null;
        $this->contentSearch = '';
        $this->selectedContentTitle = '';
        $this->showContentResults = false;
        $this->resetErrorBag('contentId');
    }

    protected function resetCtaLinkFields(): void
    {
        $this->linkType = $this->ctaLinkDefaultType();
        $this->contentId = null;
        $this->contentSearch = '';
        $this->selectedContentTitle = '';
        $this->showContentResults = false;
        $this->url = '';
        $this->icon = 'hugeicons:link-04';
        $this->resetErrorBag('contentId');
    }

    protected function loadCtaLinkFieldsFromData(array $data, ?Content $link = null): void
    {
        $this->linkType = $link ? CtaLink::typeKey($link) : CtaLink::typeKeyFromStoredData($data);

        if (! in_array($this->linkType, $this->ctaLinkAllowedTypeKeys(), true)) {
            $this->linkType = $this->ctaLinkDefaultType();
        }

        $this->contentId = filled($data['content_id'] ?? null) ? (int) $data['content_id'] : null;
        $this->selectedContentTitle = $this->contentId
            ? (Content::query()->find($this->contentId)?->title ?? '')
            : '';
        $this->contentSearch = $this->selectedContentTitle;
        $this->showContentResults = false;
        $this->url = (string) ($data['url'] ?? '');
        $this->icon = (string) ($data['icon'] ?? 'hugeicons:link-04');
    }

    protected function findPickableContent(int $id): ?Content
    {
        if (! str_starts_with($this->linkType, 'item:')) {
            return null;
        }

        $contentType = substr($this->linkType, 5);

        return Content::query()->type(CtaLink::modelType($contentType))->whereKey($id)->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function ctaLinkFieldRules(string $nameField = 'label'): array
    {
        $rules = [
            'linkType' => 'required|in:'.implode(',', $this->ctaLinkAllowedTypeKeys()),
        ];

        if ($this->ctaLinkProfile() === 'block') {
            $rules['title'] = 'nullable|string|max:255';
            $rules['description'] = 'nullable|string|max:500';
        } else {
            $rules[$nameField] = 'nullable|string|max:100';
        }

        if ($this->isExternalLink()) {
            if ($this->ctaLinkProfile() !== 'block') {
                $rules[$nameField] = 'required|string|max:100';
            } else {
                $rules['title'] = 'required|string|max:255';
            }
            $rules['url'] = 'required|url|max:500';
            $rules['icon'] = 'nullable|string|max:100';
        }

        if ($this->needsContentPicker()) {
            $rules['contentId'] = 'required|integer';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function ctaLinkFieldMessages(): array
    {
        return [
            'linkType.required' => 'يرجى اختيار نوع الرابط.',
            'contentId.required' => 'يرجى اختيار '.$this->contentPickerLabel().' من نتائج البحث.',
        ];
    }

    protected function validateCtaLinkFields(string $nameField = 'label'): bool
    {
        $this->validate($this->ctaLinkFieldRules($nameField), $this->ctaLinkFieldMessages());

        if ($this->needsContentPicker() && ! $this->findPickableContent((int) $this->contentId)) {
            $this->addError('contentId', 'يرجى اختيار عنصر صالح من القائمة.');

            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildCtaLinkData(string $nameField = 'label'): array
    {
        $parsed = CtaLink::parseTypeKey($this->linkType);

        $data = [
            'link_type' => $parsed['link_type'],
            'content_type' => $parsed['content_type'],
            'content_id' => $this->needsContentPicker() ? $this->contentId : null,
        ];

        if ($this->ctaLinkProfile() === 'block') {
            $data['title'] = $this->title;
            $data['description'] = $this->description;
            $data['url'] = $this->isExternalLink() ? $this->url : null;
            $data['icon'] = $this->isExternalLink()
                ? ($this->icon ?: 'hugeicons:link-04')
                : null;
        } else {
            $data['label'] = $this->label;
            $data['url'] = $this->isExternalLink() ? $this->url : null;
            $data['icon'] = $this->isExternalLink() ? $this->icon : null;
        }

        return $data;
    }

    /**
     * @return Collection<int, Content>
     */
    protected function ctaLinkContentResults(): Collection
    {
        if (! $this->needsContentPicker() || ! $this->showContentResults) {
            return collect();
        }

        return mb_strlen(trim($this->contentSearch)) >= 2
            ? CtaLink::searchContents($this->linkType, $this->contentSearch)
            : CtaLink::recentContents($this->linkType);
    }

    protected function applyBlockLinkTypeDefaults(): void
    {
        $this->title = CtaLink::blockLinkTitleFromTypeKey($this->linkType);
        $this->description = CtaLink::blockLinkDescriptionFromTypeKey($this->linkType);
    }

    public function updatedLinkType(): void
    {
        $this->contentId = null;
        $this->contentSearch = '';
        $this->selectedContentTitle = '';
        $this->showContentResults = false;
        $this->url = '';
        $this->resetErrorBag('contentId');

        if ($this->ctaLinkProfile() === 'block') {
            if (filled($this->linkType)) {
                $this->applyBlockLinkTypeDefaults();
            } else {
                $this->title = '';
                $this->description = '';
            }
        }
    }
}
