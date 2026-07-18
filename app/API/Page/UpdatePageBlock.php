<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\BlockBrandMark;
use App\Support\BlockTypeRegistry;
use App\Support\BusinessDocuments;
use App\Support\CtaLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Updates a page block's settings (type-specific).
 */
class UpdatePageBlock
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageBlocks;

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
            'top-nav' => [
                'show_share_button' => ['required', 'boolean'],
                'show_theme_toggle' => ['required', 'boolean'],
                'show_language_switcher' => ['required', 'boolean'],
                'show_back_button' => ['required', 'boolean'],
                'show_pages_menu' => ['required', 'boolean'],
                'show_client_login' => ['required', 'boolean'],
                'client_login_label' => ['nullable', 'string', 'max:100', 'required_if:show_client_login,true'],
            ],
            'float-links' => [
                'position' => ['required', 'string', Rule::in(['bottom-start', 'bottom-end'])],
                'show_whatsapp' => ['required', 'boolean'],
                'whatsapp_number' => ['nullable', 'string', 'max:30'],
                'show_phone' => ['required', 'boolean'],
                'phone_number' => ['nullable', 'string', 'max:30'],
            ],
            'header' => [
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'bio' => ['nullable', 'string', 'max:250'],
                'country' => ['nullable', 'string', 'max:100'],
                'city' => ['nullable', 'string', 'max:100'],
                'show_avatar' => ['sometimes', 'boolean'],
                'show_verified_badge' => ['sometimes', 'boolean'],
                'logo' => ['nullable', 'image', 'max:15024'],
                'brand_mark_type' => ['nullable', 'string', Rule::in(['image', 'emoji', 'icon', 'none'])],
                'brand_mark_value' => ['nullable', 'string', 'max:64'],
                'brand_mark_color' => ['nullable', 'string', 'max:20'],
                'remove_logo' => ['sometimes', 'boolean'],
            ],
            'footer' => [
                'show_documents_warranties' => ['required', 'boolean'],
                'document_numbers' => ['sometimes', 'array'],
                'document_numbers.*' => ['nullable', 'string', 'max:50'],
            ],
            'block-link' => [
                'link_type' => ['required', 'string', Rule::in(CtaLink::allowedBlockLinkTypeKeys())],
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:500'],
                'url' => ['nullable', 'url', 'max:500'],
                'content_id' => ['nullable', 'integer'],
                'logo' => ['nullable', 'image', 'max:15024'],
                'brand_mark_type' => ['nullable', 'string', Rule::in(['image', 'emoji', 'icon', 'none'])],
                'brand_mark_value' => ['nullable', 'string', 'max:64'],
                'brand_mark_color' => ['nullable', 'string', 'max:20'],
                'remove_logo' => ['sometimes', 'boolean'],
            ],
            'cta' => [],
            default => [
                'data' => ['sometimes', 'array'],
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $id, array $data, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $block = $this->findTenantRootBlock($id);

        if (! $block) {
            throw new NotFoundHttpException;
        }

        match ($block->type) {
            'top-nav' => $this->updateTopNav($block, $data),
            'float-links' => $this->updateFloatLinks($block, $tenant, $data),
            'header' => $this->updateHeader($block, $tenant, $data),
            'footer' => $this->updateFooter($block, $data),
            'block-link' => $this->updateBlockLink($block, $tenant, $data),
            'cta' => null,
            default => $block->update(['data' => $data['data'] ?? $block->data]),
        };

        return ShowPageBlock::make()->handle($tenant, $block->id, $blockTypes);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, int $id, BlockTypeRegistry $blockTypes): array
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

        return $this->handle($tenant, $id, $validated, $blockTypes);
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
        $id = (int) request()->route('id');

        if ($id <= 0) {
            return null;
        }

        $user = request()->user();
        $tenant = $user?->currentTenant;

        if (! $tenant instanceof Tenant) {
            return null;
        }

        setCurrentTenant($tenant);

        return $this->findTenantRootBlock($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateTopNav(Block $block, array $data): void
    {
        $block->update([
            'data' => [
                'show_share_button' => (bool) $data['show_share_button'],
                'show_theme_toggle' => (bool) $data['show_theme_toggle'],
                'show_language_switcher' => (bool) $data['show_language_switcher'],
                'show_back_button' => (bool) $data['show_back_button'],
                'show_pages_menu' => (bool) $data['show_pages_menu'],
                'show_client_login' => (bool) $data['show_client_login'],
                'client_login_label' => (string) ($data['client_login_label'] ?? 'دخول العملاء'),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateFloatLinks(Block $block, Tenant $tenant, array $data): void
    {
        app(TenantProfileService::class)->saveContact($tenant, [
            'whatsapp' => (string) ($data['whatsapp_number'] ?? ''),
            'phone' => (string) ($data['phone_number'] ?? ''),
        ]);

        $block->update([
            'data' => [
                'position' => $data['position'],
                'show_whatsapp' => (bool) $data['show_whatsapp'],
                'show_phone' => (bool) $data['show_phone'],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateHeader(Block $block, Tenant $tenant, array $data): void
    {
        $tenant->name = $data['name'];
        $tenant->save();

        $profile = app(TenantProfileService::class);
        $profile->saveBio($tenant, (string) ($data['bio'] ?? ''));

        $logo = $data['logo'] ?? null;
        $markType = (string) ($data['brand_mark_type'] ?? '');

        if ($logo instanceof UploadedFile) {
            $path = $logo->storePublicly('tenant-media/'.$tenant->uuid.'/logo', 'spaces');
            $profile->saveLogo($tenant, $path);
        } elseif ((bool) ($data['remove_logo'] ?? false) || $markType === 'none') {
            $profile->clearBrandMark($tenant);
        } elseif (in_array($markType, ['emoji', 'icon'], true)) {
            $profile->saveBrandMark($tenant, [
                'type' => $markType,
                'value' => (string) ($data['brand_mark_value'] ?? ''),
                'color' => (string) ($data['brand_mark_color'] ?? ''),
            ]);
        }

        $profile->saveContact($tenant, [
            'country' => $data['country'] ?? '',
            'city' => $data['city'] ?? '',
        ]);

        $block->update([
            'data' => [
                'show_avatar' => (bool) ($data['show_avatar'] ?? true),
                'show_verified_badge' => (bool) ($data['show_verified_badge'] ?? true),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateFooter(Block $block, array $data): void
    {
        $blockData = is_array($block->data) ? $block->data : [];

        $blockData['show_documents_warranties'] = (bool) $data['show_documents_warranties'];
        $blockData['show_eqleem_logo'] = true;

        if (array_key_exists('document_numbers', $data)) {
            $blockData['document_numbers'] = BusinessDocuments::sanitizeNumbers(
                is_array($data['document_numbers']) ? $data['document_numbers'] : []
            );
        }

        $block->update(['data' => $blockData]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateBlockLink(Block $block, Tenant $tenant, array $data): void
    {
        $linkType = (string) $data['link_type'];
        $parsed = CtaLink::parseTypeKey($linkType);
        $isExternal = CtaLink::isExternalLink($linkType);
        $needsPicker = CtaLink::needsContentPicker($linkType);
        $contentId = $needsPicker ? (int) ($data['content_id'] ?? 0) : null;

        if ($parsed['link_type'] === 'section' && filled($parsed['content_type'])
            && CtaLink::contentTypeRequiresItem((string) $parsed['content_type'])) {
            throw new UnprocessableEntityHttpException('يجب اختيار مادة محددة لهذا النوع من الروابط.');
        }

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

        $existingData = is_array($block->data) ? $block->data : [];
        $existingMark = is_array($existingData['brand_mark'] ?? null)
            ? $existingData['brand_mark']
            : null;

        $payload = [
            'link_type' => $parsed['link_type'],
            'content_type' => $parsed['content_type'],
            'content_id' => $contentId,
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'url' => $isExternal ? (string) ($data['url'] ?? '') : null,
            'icon' => $isExternal ? (string) ($data['icon'] ?? config('cta-link-types.icons.external')) : null,
        ];

        $brandMark = BlockBrandMark::resolveStored($tenant, (int) $block->id, $data, $existingMark);

        if ($brandMark !== null) {
            $payload['brand_mark'] = $brandMark;
        }

        $blockTitle = filled($payload['title'])
            ? $payload['title']
            : CtaLink::titleFromData($payload);

        $block->update([
            'title' => $blockTitle ?: $block->title,
            'data' => $payload,
        ]);
    }
}
