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
 * Creates a draft form (title only).
 */
class CreateForm
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesForm;

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
            'type' => $this->formType(),
            'title' => $title,
            'slug' => $this->uniqueFormSlug($this->slugifyTitle($title)),
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

    public function jsonResponse(Content $content): FormResource
    {
        return (new FormResource($content->fresh(), [
            'slug_prefix' => $this->slugPrefix(),
            'field_type_options' => $this->fieldTypeOptions(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
