<?php

namespace App\API\Clients;

use App\Http\Resources\ClientOrderListResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists orders belonging to a specific client for the current tenant.
 */
class ListClientOrders
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
            'status' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(Order::statusOptions()))],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function handle(Tenant $tenant, Client $client, ?string $search = null, ?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Order::query()
            ->where('orders.client_id', $client->id)
            ->where('orders.tenant_id', $tenant->id)
            ->leftJoinSub(
                DB::table('order_items')
                    ->select('order_id', DB::raw('COUNT(*) as items_count'))
                    ->groupBy('order_id'),
                'order_items_count',
                'orders.id',
                '=',
                'order_items_count.order_id'
            )
            ->select([
                'orders.id',
                'orders.uuid',
                'orders.number',
                'orders.status',
                'orders.payment_status',
                'orders.grand_total',
                'orders.currency_code',
                'orders.issued_at',
                'orders.created_at',
                DB::raw('COALESCE(order_items_count.items_count, 0) as items_count'),
            ])
            ->orderByDesc('orders.id');

        $this->applyStatusFilter($query, $status);
        $this->applySearch($query, $search);

        return $query->paginate($perPage);
    }

    public function asController(ActionRequest $request, string $uuid): LengthAwarePaginator
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Tenant $tenant */
        $tenant = $user->currentTenant;

        $client = ShowClient::run($tenant, $uuid);

        /** @var array{search?: string|null, status?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        $status = isset($validated['status']) ? trim((string) $validated['status']) : null;

        return $this->handle(
            $tenant,
            $client,
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            $status !== '' ? $status : null,
            (int) ($validated['per_page'] ?? 20),
        );
    }

    public function jsonResponse(LengthAwarePaginator $orders): AnonymousResourceCollection
    {
        return ClientOrderListResource::collection($orders);
    }

    private function applyStatusFilter(Builder $query, ?string $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        $query->where('orders.status', $status);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $query->where('orders.number', 'like', '%'.$search.'%');
    }
}
