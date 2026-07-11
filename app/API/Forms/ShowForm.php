<?php

namespace App\API\Forms;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Forms\Concerns\ResolvesForm;
use App\Http\Resources\FormResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a form for editing.
 */
class ShowForm
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesForm;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        return $this->findForm($uuid)->fresh();
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): FormResource
    {
        return new FormResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'field_type_options' => $this->fieldTypeOptions(),
        ]);
    }
}
