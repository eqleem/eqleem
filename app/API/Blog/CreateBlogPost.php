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
 * Creates a draft blog post (title only).
 */
class CreateBlogPost
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function handle(Tenant $tenant, string $title): Content
    {
        setCurrentTenant($tenant);

        return Content::query()->create([
            'tenant_id' => $tenant->id,
            'type' => $this->blogType(),
            'title' => $title,
            'slug' => $this->uniqueBlogSlug($this->slugifyTitle($title)),
            'status' => 'draft',
            'active' => true,
        ]);
    }

    public function asController(ActionRequest $request): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{title: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, trim($validated['title']));
    }

    public function jsonResponse(Content $content): BlogPostResource
    {
        return (new BlogPostResource($content->fresh(), [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
