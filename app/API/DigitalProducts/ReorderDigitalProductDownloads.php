<?php

namespace App\API\DigitalProducts;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\DigitalProducts\Concerns\ResolvesDigitalProduct;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Reorders digital product download files.
 */
class ReorderDigitalProductDownloads
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesDigitalProduct;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:30,1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->orderRules();
    }

    /**
     * @param  list<int>  $order
     * @return array{downloads: list<array{id: int, name: string, url: string, size: int}>}
     */
    public function handle(Tenant $tenant, string $uuid, array $order): array
    {
        setCurrentTenant($tenant);

        $content = $this->findDigitalProduct($uuid);
        $content->reorderMediaCollection(Content::MEDIA_DIGITAL_PRODUCT_DOWNLOADS, $order);

        return [
            'downloads' => $content->reloadMediaCollection(Content::MEDIA_DIGITAL_PRODUCT_DOWNLOADS)->digitalProductDownloadFiles(),
        ];
    }

    /**
     * @return array{downloads: list<array{id: int, name: string, url: string, size: int}>}
     */
    public function asController(ActionRequest $request, string $uuid): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{order: list<int>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated['order']);
    }

    /**
     * @param  array{downloads: list<array{id: int, name: string, url: string, size: int}>}  $result
     * @return array{data: array{downloads: list<array{id: int, name: string, url: string, size: int}>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Saved'),
        ];
    }
}
