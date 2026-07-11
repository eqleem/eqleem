<?php

namespace App\API\Blog;

use App\API\Blog\Concerns\ResolvesBlogPost;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Removes the blog post featured image.
 */
class DeleteBlogFeaturedImage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesBlogPost;

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

        $content = $this->findBlogPost($uuid);
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
