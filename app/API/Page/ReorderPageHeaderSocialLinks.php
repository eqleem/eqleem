<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Reorders header social links.
 */
class ReorderPageHeaderSocialLinks
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'string'],
        ];
    }

    /**
     * @param  list<string>  $order
     * @return list<array<string, mixed>>
     */
    public function handle(Tenant $tenant, array $order): array
    {
        setCurrentTenant($tenant);

        $items = collect(array_values($order))
            ->map(fn (string $id, int $index): array => [
                'order' => $index + 1,
                'value' => $id,
            ])
            ->all();

        app(TenantProfileService::class)->updateSocialOrder($tenant, $items);

        return app(TenantProfileService::class)->socialLinks($tenant->fresh())->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{order: list<string>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated['order']);
    }

    /**
     * @param  list<array<string, mixed>>  $links
     */
    public function jsonResponse(array $links): JsonResponse
    {
        return response()->json([
            'data' => $links,
            'message' => __('Settings updated successfully.'),
        ]);
    }
}
