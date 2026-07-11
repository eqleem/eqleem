<?php

namespace App\API\Clients;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a single client for the authenticated user's current tenant.
 */
class ShowClient
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

    public function handle(Tenant $tenant, string $uuid): Client
    {
        setCurrentTenant($tenant);

        $client = Client::query()
            ->withoutGlobalScope('tenantable')
            ->select([
                'clients.id',
                'clients.uuid',
                'clients.name',
                'clients.email',
                'clients.phone',
                'clients.address',
                'clients.city',
                'clients.neighborhood',
                'clients.notes',
                'clients.active',
                'clients.meta',
            ])
            ->where('clients.uuid', $uuid)
            ->whereExists(function ($sub) use ($tenant): void {
                $sub->selectRaw('1')
                    ->from('tenantables')
                    ->whereColumn('tenantables.tenantable_id', 'clients.id')
                    ->where('tenantables.tenantable_type', (new Client)->getMorphClass())
                    ->where('tenantables.tenant_id', $tenant->id);
            })
            ->first();

        if (! $client instanceof Client) {
            throw (new ModelNotFoundException)->setModel(Client::class, [$uuid]);
        }

        return $client;
    }

    public function asController(ActionRequest $request, string $uuid): Client
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Tenant $tenant */
        $tenant = $user->currentTenant;

        return $this->handle($tenant, $uuid);
    }

    public function jsonResponse(Client $client): ClientResource
    {
        return new ClientResource($client);
    }
}
