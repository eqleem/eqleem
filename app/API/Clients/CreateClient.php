<?php

namespace App\API\Clients;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Support\DashboardStats;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates (or attaches) a client for the authenticated user's current tenant.
 */
class CreateClient
{
    use AsAction;

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
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'phone' => ['required_without:email', 'nullable', 'string', 'max:14'],
            'email' => ['required_without:phone', 'nullable', 'email', 'max:255'],
        ];
    }

    /**
     * @param  array{name: string, phone?: string|null, email?: string|null}  $data
     */
    public function handle(Tenant $tenant, array $data): Client
    {
        setCurrentTenant($tenant);

        $phone = filled($data['phone'] ?? null) ? (string) $data['phone'] : null;
        $email = filled($data['email'] ?? null) ? (string) $data['email'] : null;

        $client = Client::withoutGlobalScope('tenantable')
            ->when(
                $phone || $email,
                fn ($query) => $query->where(function ($query) use ($phone, $email): void {
                    if ($phone) {
                        $query->orWhere('phone', $phone);
                    }

                    if ($email) {
                        $query->orWhere('email', $email);
                    }
                }),
            )
            ->first();

        if (! $client instanceof Client) {
            $client = Client::withoutGlobalScope('tenantable')->create([
                'name' => $data['name'],
                'phone' => $phone,
                'email' => $email,
                'tenant_id' => $tenant->id,
                'active' => true,
            ]);
        }

        $client->tenants()->sync(
            [
                $tenant->id => [
                    'active' => true,
                    'meta' => [
                        'name' => $data['name'],
                        'email' => $email,
                        'phone' => $phone,
                    ],
                ],
            ],
            false,
        );

        DashboardStats::forget($tenant);

        return $client->refresh();
    }

    public function asController(ActionRequest $request): Client
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Tenant $tenant */
        $tenant = $user->currentTenant;

        /** @var array{name: string, phone?: string|null, email?: string|null} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated);
    }

    public function jsonResponse(Client $client): ClientResource
    {
        return (new ClientResource($client))
            ->additional([
                'message' => __('Client saved successfully.'),
            ]);
    }
}
