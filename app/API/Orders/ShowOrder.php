<?php

namespace App\API\Orders;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a single order for the authenticated user's current tenant.
 */
class ShowOrder
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, string $uuid): Order
    {
        setCurrentTenant($tenant);

        $order = Order::query()
            ->select([
                'orders.id',
                'orders.uuid',
                'orders.number',
                'orders.status',
                'orders.payment_status',
                'orders.channel',
                'orders.currency_code',
                'orders.subtotal',
                'orders.discount_total',
                'orders.tax_total',
                'orders.grand_total',
                'orders.paid_total',
                'orders.due_total',
                'orders.notes',
                'orders.meta',
                'orders.client_id',
                'orders.issued_at',
                'orders.created_at',
                'orders.tenant_id',
            ])
            ->with([
                'client:id,uuid,name,email,phone,meta',
                'payments' => fn ($query) => $query
                    ->select([
                        'id',
                        'uuid',
                        'order_id',
                        'amount',
                        'currency',
                        'source_type',
                        'initial_status',
                        'meta',
                        'created_at',
                    ])
                    ->latest('id'),
            ])
            ->where('orders.tenant_id', $tenant->id)
            ->where('orders.uuid', $uuid)
            ->first();

        if (! $order instanceof Order) {
            throw (new ModelNotFoundException)->setModel(Order::class, [$uuid]);
        }

        return $order;
    }

    public function asController(ActionRequest $request, string $uuid): Order
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Order $order): OrderResource
    {
        return new OrderResource($order);
    }
}
