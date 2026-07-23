<?php

namespace App\API\Page\Concerns;

use App\Models\Block;
use App\Models\Content;
use App\Services\TenantProfileService;
use App\Support\BlockBrandMark;
use App\Support\BlockType;
use App\Support\BlockTypeRegistry;
use App\Support\BusinessDocuments;
use App\Support\ContentTypeRegistry;
use App\Support\CtaBooking;
use App\Support\CtaLink;
use Illuminate\Support\Collection;

trait MapsPageBlocks
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapBlocks(Collection $blocks, BlockTypeRegistry $blockTypes): Collection
    {
        $typeIcons = $blockTypes->iconPaths();
        $editors = $blockTypes->editors();
        $typeNames = $blockTypes->all()
            ->mapWithKeys(fn (BlockType $blockType): array => [$blockType->slug => $blockType->name]);
        $contentTypes = app(ContentTypeRegistry::class);

        return $blocks->map(function (Block $block) use ($typeIcons, $editors, $typeNames, $contentTypes): array {
            $icon = $typeIcons[$block->type] ?? 'assets/icons/tabler/Blockquote.svg';
            $data = is_array($block->data) ? $block->data : [];
            $contentManage = $block->type === 'block-link'
                ? $this->dashboardManageMetaFromData($data)
                : null;

            $brandMark = null;

            if ($block->type === 'block-link') {
                $icon = $this->displayIconForBlockLink($data, $icon, $contentTypes);
                $brandMark = BlockBrandMark::forEditor(
                    is_array($data['brand_mark'] ?? null) ? $data['brand_mark'] : null
                );
            }

            $linkKind = null;

            if ($block->type === 'block-link') {
                $storedLinkType = (string) ($data['link_type'] ?? '');
                $linkKind = match ($storedLinkType) {
                    'section', 'item', 'external' => $storedLinkType,
                    default => null,
                };
            }

            return [
                'id' => $block->id,
                'uuid' => $block->uuid,
                'title' => $block->is_default
                    ? ($typeNames[$block->type] ?? $block->title)
                    : $block->title,
                'type' => $block->type,
                'sort_order' => $block->sort_order,
                'is_default' => (bool) $block->is_default,
                'editable' => filled($editors[$block->type] ?? null),
                'active' => (bool) $block->active,
                'icon' => $icon,
                'icon_url' => asset($icon),
                'brand_mark' => $brandMark,
                'content_manage_url' => $contentManage['url'] ?? null,
                'content_manage_label' => $contentManage['label'] ?? null,
                'managed_section' => $block->type === 'block-link'
                    && (bool) ($data['managed_section'] ?? false),
                'link_kind' => $linkKind,
            ];
        });
    }

    /**
     * Prefer the selected section/item content-type icon; keep the block-type icon for external links.
     *
     * @param  array<string, mixed>  $data
     */
    protected function displayIconForBlockLink(array $data, string $blockTypeIcon, ContentTypeRegistry $contentTypes): string
    {
        $linkType = (string) ($data['link_type'] ?? '');

        if ($linkType === 'external' || $linkType === '') {
            return $blockTypeIcon;
        }

        $contentTypeSlug = (string) ($data['content_type'] ?? '');

        if ($contentTypeSlug === '') {
            return $blockTypeIcon;
        }

        $contentType = $contentTypes->find($contentTypeSlug);

        return filled($contentType?->icon) ? $contentType->icon : $blockTypeIcon;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function blocksForTypes(Collection $blocks, array $types): Collection
    {
        return collect($types)
            ->map(fn (string $type): ?array => $blocks->firstWhere('type', $type))
            ->filter()
            ->values();
    }

    /**
     * @return array{
     *     top: Collection<int, array<string, mixed>>,
     *     cta: ?array<string, mixed>,
     *     user: Collection<int, array<string, mixed>>,
     *     bottom: Collection<int, array<string, mixed>>,
     *     float_links: ?array<string, mixed>
     * }
     */
    protected function groupedBlocks(BlockTypeRegistry $blockTypes): array
    {
        $blocks = Block::queryForTenantRoots()
            ->orderBy('sort_order')
            ->get(['id', 'uuid', 'title', 'type', 'sort_order', 'is_default', 'active', 'data']);

        $mapped = $this->mapBlocks($blocks, $blockTypes);
        $system = $mapped->where('is_default', true)->values();
        $cta = $this->blocksForTypes($system, ['cta'])->first();
        $footer = $this->blocksForTypes($system, ['footer'])->first();
        $floatLinks = $this->blocksForTypes($system, ['float-links'])->first();

        if ($cta !== null) {
            $ctaModel = $blocks->firstWhere('id', $cta['id']);
            $cta['editor'] = $ctaModel instanceof Block
                ? $this->ctaEditorPayload($ctaModel)
                : [
                    'type' => 'cta',
                    'links' => [],
                    'link_type_options' => CtaLink::linkTypeOptions('nav'),
                    'link_type_picker_options' => CtaLink::blockLinkPickerOptions(),
                    'booking_targets' => [
                        'branches' => CtaBooking::branchOptions(),
                        'calendars' => CtaBooking::calendarOptions(),
                    ],
                ];
        }

        if ($floatLinks !== null) {
            $floatLinksModel = $blocks->firstWhere('id', $floatLinks['id']);
            $floatLinks['editor'] = $floatLinksModel instanceof Block
                ? $this->floatLinksEditorPayload($floatLinksModel)
                : $this->floatLinksEditorPayloadFromData([]);
        }

        if ($footer !== null) {
            $footerModel = $blocks->firstWhere('id', $footer['id']);
            $footer['editor'] = $footerModel instanceof Block
                ? $this->footerEditorPayload($footerModel)
                : $this->footerEditorPayloadFromData([]);
        }

        return [
            'top' => $this->blocksForTypes($system, ['top-nav', 'header']),
            'cta' => $cta,
            'user' => $mapped->where('is_default', false)->values(),
            'bottom' => collect($footer ? [$footer] : []),
            'float_links' => $floatLinks,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function ctaEditorPayload(Block $block): array
    {
        return [
            'type' => 'cta',
            'links' => $this->mapBlockLinks($block, 'cta-link'),
            'link_type_options' => CtaLink::linkTypeOptions('nav'),
            'link_type_picker_options' => CtaLink::blockLinkPickerOptions(),
            'booking_targets' => [
                'branches' => CtaBooking::branchOptions(),
                'calendars' => CtaBooking::calendarOptions(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function footerEditorPayload(Block $block): array
    {
        return $this->footerEditorPayloadFromData(
            is_array($block->data) ? $block->data : [],
            $this->mapBlockLinks($block, 'footer-link')
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $links
     * @return array<string, mixed>
     */
    protected function footerEditorPayloadFromData(array $data, array $links = []): array
    {
        return [
            'type' => 'footer',
            'show_documents_warranties' => (bool) ($data['show_documents_warranties'] ?? true),
            'show_eqleem_logo' => true,
            'documents' => BusinessDocuments::forEditor($data),
            'document_type_options' => BusinessDocuments::typeOptions(),
            'document_numbers' => BusinessDocuments::numbersFromBlockData($data),
            'business_documents' => collect(BusinessDocuments::definitions())
                ->map(fn (array $document, string $key): array => [
                    'key' => $key,
                    'label' => $document['label'],
                    'logo' => $document['logo'],
                ])
                ->values()
                ->all(),
            'links' => $links,
            'link_type_options' => CtaLink::linkTypeOptions('nav'),
            'link_type_picker_options' => CtaLink::blockLinkPickerOptions(),
            'booking_targets' => [
                'branches' => CtaBooking::branchOptions(),
                'calendars' => CtaBooking::calendarOptions(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function floatLinksEditorPayload(Block $block): array
    {
        return $this->floatLinksEditorPayloadFromData(is_array($block->data) ? $block->data : []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function floatLinksEditorPayloadFromData(array $data): array
    {
        $tenant = currentTenant();
        $contact = $tenant
            ? app(TenantProfileService::class)->contact($tenant)
            : ['phone' => '', 'whatsapp' => ''];

        return [
            'type' => 'float-links',
            'position' => (string) ($data['position'] ?? 'bottom-end'),
            'show_whatsapp' => (bool) ($data['show_whatsapp'] ?? true),
            'whatsapp_number' => (string) ($contact['whatsapp'] ?? ''),
            'show_phone' => (bool) ($data['show_phone'] ?? false),
            'phone_number' => (string) ($contact['phone'] ?? ''),
            'position_options' => [
                'bottom-start' => 'أسفل اليسار',
                'bottom-end' => 'أسفل اليمين',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function mapBlockLinks(Block $block, string $contentType): array
    {
        return Content::query()
            ->where('block_id', $block->id)
            ->type($contentType)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Content $link): array => $this->mapBlockLink($link))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapBlockLink(Content $link): array
    {
        $data = is_array($link->data) ? $link->data : [];

        return [
            'id' => $link->id,
            'title' => $link->title,
            'label' => CtaLink::label($link),
            'type_label' => CtaLink::typeLabel($link),
            'summary' => CtaLink::summary($link),
            'icon' => CtaLink::icon($link),
            'brand_mark' => BlockBrandMark::forEditor(
                is_array($data['brand_mark'] ?? null) ? $data['brand_mark'] : null
            ),
            'data' => $data,
            'sort_order' => $link->sort_order,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function blockLinkEditorPayload(array $data): array
    {
        $contentId = filled($data['content_id'] ?? null) ? (int) $data['content_id'] : null;
        $linkType = filled($data['link_type'] ?? null)
            ? CtaLink::typeKeyFromStoredData($data)
            : CtaLink::defaultTypeKey('block');
        $allowed = CtaLink::allowedBlockLinkTypeKeys();
        $managedSection = (bool) ($data['managed_section'] ?? false);
        $isSectionLink = str_starts_with($linkType, 'section:');

        if (! in_array($linkType, $allowed, true)) {
            $linkType = CtaLink::defaultTypeKey('block');
            $isSectionLink = false;
        }

        // "إضافة رابط" offers item/external destinations only. Managed section blocks
        // (from "إدارة المكونات") keep section picker flags so the editor does not
        // force a content picker.
        $itemsOnly = ! $managedSection && ! $isSectionLink;

        return [
            'type' => 'block-link',
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'url' => (string) ($data['url'] ?? ''),
            'link_type' => $linkType,
            'content_id' => $contentId,
            'selected_content_title' => $contentId
                ? (Content::query()->find($contentId)?->title ?? '')
                : '',
            'managed_section' => $managedSection,
            'brand_mark' => BlockBrandMark::forEditor(
                is_array($data['brand_mark'] ?? null) ? $data['brand_mark'] : null
            ),
            'link_type_options' => CtaLink::linkTypeOptions('block'),
            'link_type_picker_options' => CtaLink::blockLinkPickerOptions(itemsOnly: $itemsOnly),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{url: string, label: string}|null
     */
    protected function dashboardManageMetaFromData(array $data): ?array
    {
        $params = CtaLink::adminManageParamsFromData($data);

        if ($params === null) {
            return null;
        }

        $contentType = str_replace('content-', '', (string) ($params['tab'] ?? ''));

        if ($contentType === '') {
            return null;
        }

        $name = config("content-types.{$contentType}.name");
        $url = filled($params['item'] ?? null)
            ? '/dashboard/manage/'.$contentType.'/detail/'.$params['item']
            : '/dashboard/manage/'.$contentType;

        return [
            'url' => $url,
            'label' => filled($name) ? 'إدارة '.$name : 'إدارة المحتوى',
        ];
    }

    protected function findTenantRootBlock(int $id): ?Block
    {
        return Block::queryForTenantRoots()->find($id);
    }

    protected function findUserBlock(int $id): ?Block
    {
        return Block::queryForTenantRoots()->userBlocks()->find($id);
    }
}
