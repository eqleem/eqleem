<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Support\CtaLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Creates or updates a CTA/footer link belonging to a page block.
 */
class UpsertPageBlockLink
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageBlocks;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'link_type' => ['required', 'string', Rule::in(array_keys(CtaLink::linkTypeOptions('nav')))],
            'label' => ['nullable', 'string', 'max:100'],
            'url' => ['nullable', 'url', 'max:500'],
            'icon' => ['nullable', 'string', 'max:100'],
            'content_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $blockId, array $data, ?int $linkId = null): array
    {
        setCurrentTenant($tenant);

        $block = $this->findTenantRootBlock($blockId);

        if (! $block || ! in_array($block->type, ['cta', 'footer'], true)) {
            throw new NotFoundHttpException;
        }

        $contentType = $block->type === 'footer' ? 'footer-link' : 'cta-link';
        $linkType = (string) $data['link_type'];
        $parsed = CtaLink::parseTypeKey($linkType);
        $isExternal = CtaLink::isExternalLink($linkType);
        $needsPicker = CtaLink::needsContentPicker($linkType);

        if ($isExternal && (! filled($data['label'] ?? null) || ! filled($data['url'] ?? null))) {
            throw new UnprocessableEntityHttpException('الرابط الخارجي يتطلب اسماً وعنواناً.');
        }

        $contentId = null;

        if ($needsPicker) {
            $contentId = (int) ($data['content_id'] ?? 0);
            $itemType = substr($linkType, 5);
            $content = Content::query()->type(CtaLink::modelType($itemType))->whereKey($contentId)->first();

            if (! $content) {
                throw new UnprocessableEntityHttpException('يرجى اختيار عنصر صالح من القائمة.');
            }
        }

        $payload = [
            'link_type' => $parsed['link_type'],
            'content_type' => $parsed['content_type'],
            'content_id' => $contentId,
            'label' => (string) ($data['label'] ?? ''),
            'url' => $isExternal ? (string) ($data['url'] ?? '') : null,
            'icon' => $isExternal ? (string) ($data['icon'] ?? 'hugeicons:link-04') : null,
        ];

        $title = filled($payload['label'])
            ? $payload['label']
            : $this->defaultTitle($parsed, $contentId);

        if ($linkId) {
            $link = Content::query()
                ->where('block_id', $block->id)
                ->type($contentType)
                ->whereKey($linkId)
                ->first();

            if (! $link) {
                throw new NotFoundHttpException;
            }

            $link->update([
                'title' => $title,
                'data' => $payload,
            ]);
        } else {
            $maxOrder = Content::query()
                ->where('block_id', $block->id)
                ->type($contentType)
                ->max('sort_order') ?? 0;

            $link = Content::query()->create([
                'block_id' => $block->id,
                'type' => $contentType,
                'title' => $title,
                'slug' => ($block->type === 'footer' ? 'footer-' : 'cta-').Str::lower(Str::random(8)),
                'data' => $payload,
                'sort_order' => $maxOrder + 1,
                'active' => true,
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        return [
            'id' => $link->id,
            'title' => $link->title,
            'label' => CtaLink::label($link),
            'type_label' => CtaLink::typeLabel($link),
            'summary' => CtaLink::summary($link),
            'icon' => CtaLink::icon($link),
            'data' => $link->data ?? [],
            'sort_order' => $link->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, int $id, ?int $linkId = null): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $id, $validated, $linkId);
    }

    /**
     * @param  array<string, mixed>  $link
     */
    public function jsonResponse(array $link): JsonResponse
    {
        return response()->json([
            'data' => $link,
            'message' => __('Settings updated successfully.'),
        ]);
    }

    /**
     * @param  array{link_type: string, content_type: ?string}  $parsed
     */
    protected function defaultTitle(array $parsed, ?int $contentId): string
    {
        if ($parsed['link_type'] === 'section' && filled($parsed['content_type'])) {
            return (string) config('content-types.'.$parsed['content_type'].'.name', 'رابط');
        }

        if ($contentId) {
            return Content::query()->find($contentId)?->title ?? 'رابط';
        }

        return 'رابط';
    }
}
