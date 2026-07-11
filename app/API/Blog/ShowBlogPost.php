<?php

namespace App\API\Blog;

use App\API\Blog\Concerns\ResolvesBlogPost;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\BlogPostResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a blog post for editing.
 */
class ShowBlogPost
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesBlogPost;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findBlogPost($uuid);

        return $content->fresh();
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): BlogPostResource
    {
        return new BlogPostResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]);
    }
}
