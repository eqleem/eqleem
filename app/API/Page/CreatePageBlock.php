<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Http\Resources\PageBlockResource;
use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $type, BlockTypeRegistry $blockTypes): array
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

        return $this->mapBlocks(collect([$block]), $blockTypes)->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, BlockTypeRegistry $blockTypes): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{type: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated['type'], $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public function jsonResponse(array $block): PageBlockResource
    {
        return (new PageBlockResource($block))
            ->additional([
                'message' => __('Block created successfully.'),
            ]);
    }
}
