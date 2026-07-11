<?php

namespace App\API\Orders;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Updates an order's status (reason is optional).
 */
class UpdateOrderStatus
{
    use AsAction;
    use AuthorizesDashboardTenant;

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
            'status' => ['required', 'string', Rule::in(array_keys(Order::statusOptions()))],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function handle(Tenant $tenant, string $uuid, string $status, ?string $reason = null): Order
    {
        setCurrentTenant($tenant);

        $order = Order::query()
            ->where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->first();

        if (! $order instanceof Order) {
            throw (new ModelNotFoundException)->setModel(Order::class, [$uuid]);
        }

        if ($status === $order->statusValue()) {
            throw ValidationException::withMessages([
                'status' => [__('The selected status is already the current status.')],
            ]);
        }

        DB::transaction(function () use ($order, $status, $reason): void {
            $order->changeStatus($status, filled($reason) ? $reason : null);
        });

        return app(ShowOrder::class)->handle($tenant, $uuid);
    }

    public function asController(ActionRequest $request, string $uuid): Order
    {
        /** @var array{status: string, reason?: string|null} $validated */
        $validated = $request->validated();

        $reason = isset($validated['reason']) ? trim((string) $validated['reason']) : null;

        return $this->handle(
            $this->currentDashboardTenant($request),
            $uuid,
            $validated['status'],
            filled($reason) ? $reason : null,
        );
    }

    public function jsonResponse(Order $order): OrderResource
    {
        return (new OrderResource($order))
            ->additional([
                'message' => __('Order status updated successfully.'),
            ]);
    }
}
