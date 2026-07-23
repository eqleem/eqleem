<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\ResolvesPage;
use App\Http\Resources\PageListResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Toggles active state for a content page.
 */
class TogglePageActive
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesPage;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('active')) {
            $request->merge([
                'active' => filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    public function handle(Tenant $tenant, string $uuid, bool $active): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);
        $content->update([
            'active' => $active,
            'status' => $active ? 'published' : 'draft',
            'published_at' => $active
                ? ($content->published_at ?? now())
                : null,
        ]);

        return $content->fresh();
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{active: bool} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, (bool) $validated['active']);
    }

    public function jsonResponse(Content $content): PageListResource
    {
        return (new PageListResource($content))
            ->additional([
                'message' => $content->active ? 'تم تفعيل الصفحة.' : 'تم تعطيل الصفحة.',
            ]);
    }
}
