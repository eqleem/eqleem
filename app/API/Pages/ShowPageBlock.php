<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\MapsContentPageBlocks;
use App\API\Pages\Concerns\ResolvesPage;
use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use App\Support\CtaLink;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns a content page block with type-specific editor payload.
 */
class ShowPageBlock
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsContentPageBlocks;
    use ResolvesPage;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $uuid, int $id, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);
        $block = $this->findContentUserBlock($content->id, $id);

        if (! $block) {
            throw new NotFoundHttpException;
        }

        $mapped = $this->mapContentPageBlocks(collect([$block]), $blockTypes)->first();

        return [
            'block' => $mapped,
            'editor' => $this->editorPayload($block),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $uuid, int $id, BlockTypeRegistry $blockTypes): array
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid, $id, $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json(['data' => $payload]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function editorPayload(Block $block): array
    {
        $data = is_array($block->data) ? $block->data : [];

        return match ($block->type) {
            'block-link' => $this->blockLinkEditorPayload($data),
            default => [
                'type' => $block->type,
                'data' => $data,
            ],
        };
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

        if (! in_array($linkType, $allowed, true)) {
            $linkType = CtaLink::defaultTypeKey('block');
        }

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
            'link_type_options' => CtaLink::linkTypeOptions('block'),
            'link_type_picker_options' => CtaLink::blockLinkPickerOptions(itemsOnly: true),
        ];
    }
}
