<?php

namespace App\API\Newsletter;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Newsletter\Concerns\ResolvesNewsletter;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Removes the newsletter featured image.
 */
class DeleteNewsletterFeaturedImage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesNewsletter;

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
     * @return array{featured_image: null}
     */
    public function handle(Tenant $tenant, string $uuid): array
    {
        setCurrentTenant($tenant);

        $content = $this->findNewsletter($uuid);
        $data = $content->data ?? [];
        unset($data['image']);

        $content->update(['data' => $data]);

        return ['featured_image' => null];
    }

    public function asController(ActionRequest $request, string $uuid): array
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    /**
     * @param  array{featured_image: null}  $result
     * @return array{data: array{featured_image: null}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Item(s) deleted successfully.'),
        ];
    }
}
