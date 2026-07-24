<?php

namespace App\API\Portfolio;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Portfolio\Concerns\ResolvesPortfolioProject;
use App\Http\Resources\PortfolioProjectResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a portfolio project for editing.
 */
class ShowPortfolioProject
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesPortfolioProject;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findPortfolioProject($uuid);
        $content->migrateLegacyPortfolioImagesIfNeeded();
        $content->load(['media', 'taxonomies']);

        return $content;
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): PortfolioProjectResource
    {
        return new PortfolioProjectResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]);
    }
}
