<?php

namespace App\API\Clients;

use App\Http\Resources\InvoiceListResource;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists invoices for orders belonging to a specific client.
 */
class ListClientInvoices
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
     * @return LengthAwarePaginator<int, Invoice>
     */
    public function handle(Tenant $tenant, Client $client, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $orderIds = Order::query()
            ->where('tenant_id', $tenant->id)
            ->where('client_id', $client->id)
            ->select('id');

        $query = Invoice::query()
            ->select([
                'invoices.id',
                'invoices.uuid',
                'invoices.number',
                'invoices.initial_status',
                'invoices.type',
                'invoices.currency',
                'invoices.total_after_vat',
                'invoices.amount_paid',
                'invoices.issued_on',
                'invoices.created_at',
                'invoices.invoicable_type',
                'invoices.invoicable_id',
            ])
            ->with([
                'invoicable' => fn ($relation) => $relation->select(['id', 'uuid', 'number']),
            ])
            ->where('invoices.tenant_id', $tenant->id)
            ->where('invoices.invoicable_type', Order::class)
            ->whereIn('invoices.invoicable_id', $orderIds)
            ->orderByDesc('invoices.id');

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

        /** @var array{search?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            $client,
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            (int) ($validated['per_page'] ?? 20),
        );
    }

    public function jsonResponse(LengthAwarePaginator $invoices): AnonymousResourceCollection
    {
        return InvoiceListResource::collection($invoices);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $term = '%'.$search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->where('invoices.number', 'like', $term)
                ->orWhere('invoices.note', 'like', $term)
                ->orWhereIn('invoices.invoicable_id', function ($sub) use ($term): void {
                    $sub->select('id')
                        ->from('orders')
                        ->where(function ($orders) use ($term): void {
                            $orders->where('number', 'like', $term)
                                ->orWhere('id', 'like', $term);
                        });
                });
        });
    }
}
