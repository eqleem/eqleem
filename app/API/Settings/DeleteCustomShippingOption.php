<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes a custom shipping option.
 */
class DeleteCustomShippingOption
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, string $id): void
    {
        setCurrentTenant($tenant);

        $items = Setting::customShippingOptions();
        $filtered = collect($items)
            ->reject(fn (array $item): bool => ($item['id'] ?? null) === $id)
            ->values()
            ->all();

        if (count($filtered) === count($items)) {
            throw new NotFoundHttpException;
        }

        Setting::saveCustomShippingOptions($filtered);
    }

    public function asController(ActionRequest $request, string $id): void
    {
        $this->handle($this->currentDashboardTenant($request), $id);
    }

    public function jsonResponse(): JsonResponse
    {
        return response()->json([
            'message' => __('Settings updated successfully.'),
        ]);
    }
}
