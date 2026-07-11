<?php

namespace App\API\Menu;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Menu\Concerns\ResolvesMenuItem;
use App\Http\Resources\MenuItemResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a draft menu item (title only).
 */
class CreateMenuItem
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesMenuItem;

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
            'type' => $this->menuType(),
            'title' => $title,
            'slug' => $this->uniqueMenuSlug($this->slugifyTitle($title)),
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

    public function jsonResponse(Content $content): MenuItemResource
    {
        return (new MenuItemResource($content->fresh(), [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
