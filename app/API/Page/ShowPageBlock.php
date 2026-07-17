<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Models\Block;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use App\Support\BlockTypeRegistry;
use App\Support\BusinessDocuments;
use App\Support\CtaLink;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns a page block with type-specific editor payload.
 */
class ShowPageBlock
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageBlocks;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $id, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $block = $this->findTenantRootBlock($id);

        if (! $block) {
            throw new NotFoundHttpException;
        }

        $mapped = $this->mapBlocks(collect([$block]), $blockTypes)->first();

        return [
            'block' => $mapped,
            'editor' => $this->editorPayload($block, $tenant),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, int $id, BlockTypeRegistry $blockTypes): array
    {
        return $this->handle($this->currentDashboardTenant($request), $id, $blockTypes);
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
    protected function editorPayload(Block $block, Tenant $tenant): array
    {
        $data = is_array($block->data) ? $block->data : [];

        return match ($block->type) {
            'top-nav' => [
                'type' => 'top-nav',
                'show_share_button' => (bool) ($data['show_share_button'] ?? true),
                'show_theme_toggle' => (bool) ($data['show_theme_toggle'] ?? true),
                'show_language_switcher' => (bool) ($data['show_language_switcher'] ?? true),
                'show_back_button' => (bool) ($data['show_back_button'] ?? true),
                'show_pages_menu' => (bool) ($data['show_pages_menu'] ?? true),
                'show_client_login' => (bool) ($data['show_client_login'] ?? true),
                'client_login_label' => (string) ($data['client_login_label'] ?? 'دخول العملاء'),
            ],
            'float-links' => $this->floatLinksEditorPayload($block),
            'header' => $this->headerEditorPayload($block, $tenant, $data),
            'footer' => $this->footerEditorPayload($block, $data),
            'cta' => $this->ctaEditorPayload($block),
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
    protected function headerEditorPayload(Block $block, Tenant $tenant, array $data): array
    {
        $profile = app(TenantProfileService::class);
        $contact = $profile->contact($tenant);
        $networks = config('social-networks', []);

        $brandMark = $profile->brandMark($tenant);

        return [
            'type' => 'header',
            'name' => (string) ($tenant->name ?? ''),
            'logo' => (string) ($tenant->logo ?? ''),
            'brand_mark' => $brandMark,
            'bio' => $profile->bio($tenant),
            'show_avatar' => (bool) ($data['show_avatar'] ?? true),
            'show_verified_badge' => (bool) ($data['show_verified_badge'] ?? true),
            'country' => (string) ($contact['country'] ?? ''),
            'city' => (string) ($contact['city'] ?? ''),
            'social_links' => $profile->socialLinks($tenant)->values()->all(),
            'social_networks' => collect($networks)
                ->map(fn (array $network, string $key): array => [
                    'key' => $key,
                    'label' => $network['label'] ?? $key,
                    'icon' => $network['icon'] ?? 'ri:link',
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function footerEditorPayload(Block $block, array $data): array
    {
        return [
            'type' => 'footer',
            'show_documents_warranties' => (bool) ($data['show_documents_warranties'] ?? true),
            'show_eqleem_logo' => true,
            'document_numbers' => BusinessDocuments::numbersFromBlockData($data),
            'business_documents' => collect(BusinessDocuments::definitions())
                ->map(fn (array $document, string $key): array => [
                    'key' => $key,
                    'label' => $document['label'],
                    'logo' => $document['logo'],
                ])
                ->values()
                ->all(),
            'links' => $this->mapBlockLinks($block, 'footer-link'),
            'link_type_options' => CtaLink::linkTypeOptions('nav'),
            'link_type_picker_options' => CtaLink::blockLinkPickerOptions(),
        ];
    }
}
