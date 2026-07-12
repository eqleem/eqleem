<?php

namespace App\API\Orders;

use App\Http\Resources\OrderListResource;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists orders for the authenticated user's current tenant (dashboard table).
 */
class ListOrders
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
    public function handle(Tenant $tenant, ?string $search = null, ?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Order::query()
            ->select([
                'orders.id',
                'orders.uuid',
                'orders.number',
                'orders.status',
                'orders.payment_status',
                'orders.grand_total',
                'orders.currency_code',
                'orders.client_id',
                'orders.issued_at',
                'orders.created_at',
            ])
            ->with([
                'client:id,name',
            ])
            ->orderByDesc('orders.id');

        $this->applyStatusFilter($query, $status);
        $this->applySearch($query, $search);

        return $query->paginate($perPage);
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Tenant $tenant */
        $tenant = $user->currentTenant;

        /** @var array{search?: string|null, status?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        $status = isset($validated['status']) ? trim((string) $validated['status']) : null;

        return $this->handle(
            $tenant,
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            $status !== '' ? $status : null,
            (int) ($validated['per_page'] ?? 20),
        );
    }

    public function jsonResponse(LengthAwarePaginator $orders): AnonymousResourceCollection
    {
        return OrderListResource::collection($orders);
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

        $term = '%'.$search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->where('orders.number', 'like', $term)
                ->orWhereIn('orders.client_id', function ($sub) use ($term): void {
                    $sub->select('id')
                        ->from('clients')
                        ->where(function ($clients) use ($term): void {
                            $clients->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term)
                                ->orWhere('phone', 'like', $term);
                        });
                });
        });
    }
}
