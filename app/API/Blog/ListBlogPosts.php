<?php

namespace App\API\Blog;

use App\API\Blog\Concerns\ResolvesBlogPost;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\BlogPostListResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists blog posts for the current dashboard tenant.
 */
class ListBlogPosts
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesBlogPost;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->listQueryRules();
    }

    /**
     * @return LengthAwarePaginator<int, Content>
     */
    public function handle(Tenant $tenant, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Content::query()
            ->type($this->blogType())
            ->with('media')
            ->orderByDesc('id');

        if ($search !== null && $search !== '') {
            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query->paginate($perPage);
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{search?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            (int) ($validated['per_page'] ?? 20),
        );
    }

    public function jsonResponse(LengthAwarePaginator $projects): AnonymousResourceCollection
    {
        return BlogPostListResource::collection($projects);
    }
}
