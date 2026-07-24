<?php

namespace App\API\Menu;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Menu\Concerns\ResolvesMenuItem;
use App\Http\Resources\MenuItemListResource;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists menu items for the current dashboard tenant.
 */
class ListMenuItems
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesMenuItem;

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
            ->type($this->menuType())
            ->with([
                'media' => fn ($query) => $query->where('collection_name', 'menu-media'),
            ])
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
        return MenuItemListResource::collection($projects);
    }
}
