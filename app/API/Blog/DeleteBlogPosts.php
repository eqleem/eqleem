<?php

namespace App\API\Blog;

use App\API\Blog\Concerns\ResolvesBlogPost;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Soft-deletes one or more blog posts.
 */
class DeleteBlogPosts
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
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('contents', 'id')->where(function ($query): void {
                    $query->where('type', $this->blogType());

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array{deleted: int}
     */
    public function handle(Tenant $tenant, array $ids): array
    {
        setCurrentTenant($tenant);

        $deleted = 0;

        Content::query()
            ->type($this->blogType())
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Content $item) use (&$deleted): void {
                $item->delete();
                $deleted++;
            });

        return ['deleted' => $deleted];
    }

    /**
     * @return array{deleted: int}
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{ids: list<int>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated['ids']);
    }

    /**
     * @param  array{deleted: int}  $result
     * @return array{data: array{deleted: int}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Selected items deleted successfully.'),
        ];
    }
}
