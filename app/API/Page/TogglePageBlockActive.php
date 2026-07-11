<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Http\Resources\PageBlockResource;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Toggles active state for a user page block.
 */
class TogglePageBlockActive
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
            'active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, int $id, bool $active, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $block = $this->findUserBlock($id);

        if (! $block) {
            throw new NotFoundHttpException;
        }

        $block->update(['active' => $active]);

        return $this->mapBlocks(collect([$block->fresh()]), $blockTypes)->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, int $id, BlockTypeRegistry $blockTypes): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{active: bool} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $id, (bool) $validated['active'], $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public function jsonResponse(array $block): PageBlockResource
    {
        return (new PageBlockResource($block))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }
}
