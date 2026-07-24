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
 * Shows a store project for editing.
 */
class ShowStoreProduct
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesStoreProduct;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findStoreProduct($uuid);
        $content->loadMissing(['media', 'taxonomies']);

        return $content;
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): StoreProductResource
    {
        return new StoreProductResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]);
    }
}
