<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\MapsContentPageBlocks;
use App\API\Pages\Concerns\ResolvesPage;
use App\Http\Resources\PageBlockResource;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Toggles active state for a user block on a content page.
 */
class TogglePageBlockActive
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
        return [
            'active' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('active')) {
            $request->merge([
                'active' => filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $uuid, int $id, bool $active, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);
        $block = $this->findContentUserBlock($content->id, $id);

        if (! $block) {
            throw new NotFoundHttpException;
        }

        $block->update(['active' => $active]);

        return $this->mapContentPageBlocks(collect([$block->fresh()]), $blockTypes)->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $uuid, int $id, BlockTypeRegistry $blockTypes): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{active: bool} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $id, (bool) $validated['active'], $blockTypes);
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
