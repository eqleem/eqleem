<?php

namespace App\API\UnitRental;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\UnitRental\Concerns\ResolvesUnitRental;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Deletes a gallery image from a unit rental.
 */
class DeleteUnitRentalImage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesUnitRental;

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
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function handle(Tenant $tenant, string $uuid, int $mediaId): array
    {
        setCurrentTenant($tenant);

        $content = $this->findUnitRental($uuid);
        $content->deleteMediaFromCollection(Content::MEDIA_UNIT, $mediaId);

        return [
            'images' => $content->reloadMediaCollection(Content::MEDIA_UNIT)->unitImages(),
        ];
    }

    /**
     * @return array{images: list<array{id: int, url: string}>}
     */
    public function asController(ActionRequest $request, string $uuid, int $mediaId): array
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid, $mediaId);
    }

    /**
     * @param  array{images: list<array{id: int, url: string}>}  $result
     * @return array{data: array{images: list<array{id: int, url: string}>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Item(s) deleted successfully.'),
        ];
    }
}
