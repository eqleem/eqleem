<?php

namespace App\API\Store;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Store\Concerns\ResolvesStoreProduct;
use App\Http\Resources\StoreProductResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a draft store project (title only).
 */
class CreateStoreProduct
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesStoreProduct;

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
            'type' => $this->storeType(),
            'title' => $title,
            'slug' => $this->uniqueStoreSlug($this->slugifyTitle($title)),
            'status' => 'draft',
            'active' => false,
        ]);
    }

    public function asController(ActionRequest $request): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{title: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, trim($validated['title']));
    }

    public function jsonResponse(Content $content): StoreProductResource
    {
        return (new StoreProductResource($content->fresh(), [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
