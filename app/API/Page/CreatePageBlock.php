<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use App\Support\CtaLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Creates a user (non-default) page block for the current tenant.
 */
class CreatePageBlock
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageBlocks;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $addable = collect(config('block-types', []))
            ->filter(fn (array $config): bool => ! ($config['default'] ?? false))
            ->keys()
            ->all();

        return [
            'type' => ['required', 'string', Rule::in($addable)],
            'link_type' => ['sometimes', 'required', 'string', Rule::in(CtaLink::allowedBlockLinkTypeKeys())],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'url' => ['nullable', 'url', 'max:500'],
            'content_id' => ['nullable', 'integer'],
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
    public function handle(Tenant $tenant, string $type, array $data, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $blockType = $blockTypes->find($type);

        if (! $blockType || $blockType->default) {
            throw new UnprocessableEntityHttpException(__('This block type cannot be added.'));
        }

        $maxOrder = Block::queryForTenantRoots()
            ->userBlocks()
            ->max('sort_order') ?? 0;

        $block = Block::query()->create([
            'tenant_id' => $tenant->id,
            'component' => $blockType->component,
            'type' => $blockType->slug,
            'title' => $blockType->name,
            'sort_order' => $maxOrder + 1,
            'is_default' => false,
            'status' => 'draft',
            'active' => true,
            'position' => 'home',
        ]);

        try {
            if ($type === 'block-link' && filled($data['link_type'] ?? null)) {
                return UpdatePageBlock::make()->handle($tenant, $block->id, $data, $blockTypes);
            }

            return ShowPageBlock::make()->handle($tenant, $block->id, $blockTypes);
        } catch (\Throwable $exception) {
            $block->delete();

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, BlockTypeRegistry $blockTypes): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{type: string}&array<string, mixed> $validated */
        $validated = $request->validated();
        $type = $validated['type'];
        unset($validated['type']);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo');
        }

        if ($request->boolean('remove_logo')) {
            $validated['remove_logo'] = true;
        }

        return $this->handle($tenant, $type, $validated, $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): JsonResponse
    {
        return response()->json([
            'data' => $payload,
            'message' => __('Block created successfully.'),
        ]);
    }
}
