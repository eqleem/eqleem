<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Models\Block;
use App\Models\Branch;
use App\Models\Calendar;
use App\Models\Content;
use App\Models\Tenant;
use App\Support\BlockBrandMark;
use App\Support\CtaBooking;
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
            'link_type' => [
                'required',
                'string',
                Rule::in(array_values(array_filter(
                    CtaLink::allowedBlockLinkTypeKeys(),
                    fn (string $key): bool => $key !== '',
                ))),
            ],
            'label' => ['nullable', 'string', 'max:100'],
            'url' => ['nullable', 'url', 'max:500'],
            'icon' => ['nullable', 'string', 'max:100'],
            'content_id' => ['nullable', 'integer'],
            'branch_ids' => ['sometimes', 'array'],
            'branch_ids.*' => ['integer'],
            'calendar_ids' => ['sometimes', 'array'],
            'calendar_ids.*' => ['integer'],
            'allow_client_choice' => ['sometimes', 'boolean'],
            'duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:480'],
            'logo' => ['nullable', 'image', 'max:15024'],
            'brand_mark_type' => ['nullable', 'string', Rule::in(['image', 'emoji', 'icon', 'none'])],
            'brand_mark_value' => ['nullable', 'string', 'max:64'],
            'brand_mark_color' => ['nullable', 'string', 'max:20'],
            'remove_logo' => ['sometimes', 'boolean'],
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
        $isBooking = CtaLink::isBookingLink($linkType);
        $needsPicker = CtaLink::needsContentPicker($linkType);

        if ($parsed['link_type'] === 'section' && filled($parsed['content_type'])
            && CtaLink::contentTypeRequiresItem((string) $parsed['content_type'])) {
            throw new UnprocessableEntityHttpException('يجب اختيار مادة محددة لهذا النوع من الروابط.');
        }

        if ($isExternal && (! filled($data['label'] ?? null) || ! filled($data['url'] ?? null))) {
            throw new UnprocessableEntityHttpException('الرابط الخارجي يتطلب اسماً وعنواناً.');
        }

        if ($isBooking && ! filled($data['label'] ?? null)) {
            throw new UnprocessableEntityHttpException('زر حجز الموعد يتطلب عنواناً.');
        }

        $bookingConfig = null;

        if ($isBooking) {
            $bookingConfig = $this->validatedBookingConfig($data);
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

        $existing = null;

        if ($linkId) {
            $existing = Content::query()
                ->where('block_id', $block->id)
                ->type($contentType)
                ->whereKey($linkId)
                ->first();

            if (! $existing) {
                throw new NotFoundHttpException;
            }
        }

        $existingData = is_array($existing?->data) ? $existing->data : [];
        $existingMark = is_array($existingData['brand_mark'] ?? null)
            ? $existingData['brand_mark']
            : null;

        $brandMark = BlockBrandMark::resolveStored($tenant, $block->id, $data, $existingMark);

        $icon = null;

        if (($brandMark['type'] ?? '') === 'icon' && filled($brandMark['value'] ?? null)) {
            $icon = (string) $brandMark['value'];
        } elseif (filled($data['icon'] ?? null) && $brandMark === null) {
            $icon = (string) $data['icon'];
        } elseif ($isExternal && $brandMark === null) {
            $icon = (string) ($existingData['icon'] ?? 'hugeicons:link-04');
        } elseif ($isBooking && $brandMark === null) {
            $icon = (string) ($existingData['icon'] ?? config('cta-link-types.icons.booking'));
        }

        $payload = [
            'link_type' => $parsed['link_type'],
            'content_type' => $parsed['content_type'],
            'content_id' => $contentId,
            'label' => (string) ($data['label'] ?? ''),
            'url' => $isExternal ? (string) ($data['url'] ?? '') : null,
            'icon' => $icon,
            'brand_mark' => $brandMark,
        ];

        if ($bookingConfig !== null) {
            $payload = array_merge($payload, $bookingConfig);
        }

        $title = filled($payload['label'])
            ? $payload['label']
            : $this->defaultTitle($parsed, $contentId);

        if ($existing) {
            $existing->update([
                'title' => $title,
                'data' => $payload,
            ]);
            $link = $existing->fresh();
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

        return $this->mapBlockLink($link);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, int $id, ?int $linkId = null): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo');
        }

        if ($request->boolean('remove_logo')) {
            $validated['remove_logo'] = true;
        }

        if ($request->has('allow_client_choice')) {
            $validated['allow_client_choice'] = $request->boolean('allow_client_choice');
        }

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
        if ($parsed['link_type'] === 'booking') {
            return 'حجز موعد';
        }

        if ($parsed['link_type'] === 'section' && filled($parsed['content_type'])) {
            return (string) config('content-types.'.$parsed['content_type'].'.name', 'رابط');
        }

        if ($contentId) {
            return Content::query()->find($contentId)?->title ?? 'رابط';
        }

        return 'رابط';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     branch_ids: list<int>,
     *     calendar_ids: list<int>,
     *     allow_client_choice: bool,
     *     duration_minutes: int
     * }
     */
    protected function validatedBookingConfig(array $data): array
    {
        $config = CtaBooking::configFromData($data);

        if ($config['branch_ids'] === [] && $config['calendar_ids'] === []) {
            throw new UnprocessableEntityHttpException('اختر فرعاً واحداً على الأقل أو تقويماً للحجز.');
        }

        if ($config['branch_ids'] !== []) {
            $validBranchCount = Branch::query()
                ->whereIn('id', $config['branch_ids'])
                ->count();

            if ($validBranchCount !== count($config['branch_ids'])) {
                throw new UnprocessableEntityHttpException('أحد الفروع المحددة غير صالح.');
            }
        }

        if ($config['calendar_ids'] !== []) {
            $validCalendarCount = Calendar::query()
                ->whereIn('id', $config['calendar_ids'])
                ->where('active', true)
                ->count();

            if ($validCalendarCount !== count($config['calendar_ids'])) {
                throw new UnprocessableEntityHttpException('أحد التقاويم المحددة غير صالح.');
            }
        }

        $calendars = CtaBooking::resolveCalendars($config['branch_ids'], $config['calendar_ids']);

        if ($calendars->isEmpty()) {
            throw new UnprocessableEntityHttpException('لا توجد تقاويم نشطة تطابق الفروع أو التقاويم المحددة.');
        }

        return $config;
    }
}
