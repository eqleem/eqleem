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
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Updates a content page block's settings (type-specific).
 */
class UpdatePageBlock
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsContentPageBlocks;
    use ResolvesPage;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $block = $this->blockFromRequest();

        if (! $block) {
            return [];
        }

        return match ($block->type) {
            'block-link' => [
                'link_type' => ['required', 'string', Rule::in(CtaLink::allowedBlockLinkTypeKeys())],
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:500'],
                'url' => ['nullable', 'url', 'max:500'],
                'content_id' => ['nullable', 'integer'],
            ],
            default => [
                'data' => ['sometimes', 'array'],
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $uuid, int $id, array $data, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);
        $block = $this->findContentUserBlock($content->id, $id);

        if (! $block) {
            throw new NotFoundHttpException;
        }

        match ($block->type) {
            'block-link' => $this->updateBlockLink($block, $data),
            default => $block->update(['data' => $data['data'] ?? $block->data]),
        };

        return ShowPageBlock::make()->handle($tenant, $uuid, $block->id, $blockTypes);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $uuid, int $id, BlockTypeRegistry $blockTypes): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $id, $validated, $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json([
            'data' => $payload,
            'message' => __('Settings updated successfully.'),
        ]);
    }

    protected function blockFromRequest(): ?Block
    {
        $uuid = (string) request()->route('uuid');
        $id = (int) request()->route('id');

        if ($uuid === '' || $id <= 0) {
            return null;
        }

        $user = request()->user();
        $tenant = $user?->currentTenant;

        if (! $tenant instanceof Tenant) {
            return null;
        }

        setCurrentTenant($tenant);

        try {
            $content = $this->findPage($uuid);
        } catch (NotFoundHttpException) {
            return null;
        }

        return $this->findContentUserBlock($content->id, $id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateBlockLink(Block $block, array $data): void
    {
        $linkType = (string) $data['link_type'];
        $parsed = CtaLink::parseTypeKey($linkType);
        $isExternal = CtaLink::isExternalLink($linkType);
        $needsPicker = CtaLink::needsContentPicker($linkType);
        $contentId = $needsPicker ? (int) ($data['content_id'] ?? 0) : null;

        if ($isExternal && ! filled($data['url'] ?? null)) {
            throw new UnprocessableEntityHttpException('الرابط الخارجي يتطلب عنوان URL كاملاً.');
        }

        if ($needsPicker) {
            if (! $contentId) {
                throw new UnprocessableEntityHttpException('يرجى اختيار عنصر صالح من القائمة.');
            }

            $contentType = substr($linkType, 5);
            $content = Content::query()->type(CtaLink::modelType($contentType))->whereKey($contentId)->first();

            if (! $content) {
                throw new UnprocessableEntityHttpException('يرجى اختيار عنصر صالح من القائمة.');
            }
        }

        $payload = [
            'link_type' => $parsed['link_type'],
            'content_type' => $parsed['content_type'],
            'content_id' => $contentId,
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'url' => $isExternal ? (string) ($data['url'] ?? '') : null,
            'icon' => $isExternal ? (string) ($data['icon'] ?? config('cta-link-types.icons.external')) : null,
        ];

        $blockTitle = filled($payload['title'])
            ? $payload['title']
            : CtaLink::titleFromData($payload);

        $block->update([
            'title' => $blockTitle ?: $block->title,
            'data' => $payload,
        ]);
    }
}
