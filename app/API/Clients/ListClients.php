<?php

namespace App\API\Clients;

use App\Http\Resources\ClientListResource;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists clients attached to the authenticated user's current tenant.
 */
class ListClients
{
    use AsAction;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:60,1',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return false;
        }

        $tenant = $user->currentTenant;

        return $tenant instanceof Tenant && $user->canAccessDashboard($tenant);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Client>
     */
    public function handle(Tenant $tenant, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Client::query()
            ->withoutGlobalScope('tenantable')
            ->select([
                'clients.id',
                'clients.uuid',
                'clients.name',
                'clients.email',
                'clients.phone',
                'clients.active',
                'clients.meta',
            ])
            ->whereExists(function ($sub) use ($tenant): void {
                $sub->selectRaw('1')
                    ->from('tenantables')
                    ->whereColumn('tenantables.tenantable_id', 'clients.id')
                    ->where('tenantables.tenantable_type', (new Client)->getMorphClass())
                    ->where('tenantables.tenant_id', $tenant->id);
            })
            ->orderByDesc('clients.id');

        $this->applySearch($query, $search);

        return $query->paginate($perPage);
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Tenant $tenant */
        $tenant = $user->currentTenant;

        /** @var array{search?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            (int) ($validated['per_page'] ?? 20),
        );
    }

    public function jsonResponse(LengthAwarePaginator $clients): AnonymousResourceCollection
    {
        return ClientListResource::collection($clients);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $term = '%'.$search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->where('clients.name', 'like', $term)
                ->orWhere('clients.email', 'like', $term)
                ->orWhere('clients.phone', 'like', $term);
        });
    }
}
