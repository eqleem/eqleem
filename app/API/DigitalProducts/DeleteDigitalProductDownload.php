<?php

namespace App\API\DigitalProducts;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\DigitalProducts\Concerns\ResolvesDigitalProduct;
use App\Models\Media;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes a download file from a digital product.
 */
class DeleteDigitalProductDownload
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
     * @return array{downloads: list<array{id: int, name: string, url: string, size: int}>}
     */
    public function handle(Tenant $tenant, string $uuid, int $mediaId): array
    {
        setCurrentTenant($tenant);

        $content = $this->findDigitalProduct($uuid);
        $media = $content->getMedia('digital-product-downloads')->firstWhere('id', $mediaId);

        if (! $media instanceof Media) {
            throw new NotFoundHttpException;
        }

        $media->delete();

        return [
            'downloads' => $content->reloadMediaCollection('digital-product-downloads')->digitalProductDownloadFiles(),
        ];
    }

    /**
     * @return array{downloads: list<array{id: int, name: string, url: string, size: int}>}
     */
    public function asController(ActionRequest $request, string $uuid, int $mediaId): array
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid, $mediaId);
    }

    /**
     * @param  array{downloads: list<array{id: int, name: string, url: string, size: int}>}  $result
     * @return array{data: array{downloads: list<array{id: int, name: string, url: string, size: int}>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Item(s) deleted successfully.'),
        ];
    }
}
