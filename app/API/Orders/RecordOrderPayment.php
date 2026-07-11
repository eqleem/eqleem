<?php

namespace App\API\Orders;

use App\Actions\RecordOrderPayment as RecordOrderPaymentAction;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Tenant;
use App\Support\DashboardStats;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Records a manual payment against an order (creates invoice + updates totals).
 */
class RecordOrderPayment
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', Rule::in(array_keys(Order::paymentMethodOptions()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function handle(Tenant $tenant, string $uuid, float|string $amount, string $method, ?string $notes = null): Order
    {
        setCurrentTenant($tenant);

        $order = Order::query()
            ->where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->first();

        if (! $order instanceof Order) {
            throw (new ModelNotFoundException)->setModel(Order::class, [$uuid]);
        }

        $amountMinor = Order::minorFromDecimal($amount);

        if ($order->due_total <= 0) {
            throw ValidationException::withMessages([
                'amount' => [__('This order has no remaining balance.')],
            ]);
        }

        if ($amountMinor > $order->due_total) {
            throw ValidationException::withMessages([
                'amount' => [__('The payment amount exceeds the remaining balance.')],
            ]);
        }

        RecordOrderPaymentAction::run(
            $order,
            $amountMinor,
            $method,
            filled($notes) ? $notes : null,
        );

        DashboardStats::forget($tenant);

        return app(ShowOrder::class)->handle($tenant, $uuid);
    }

    public function asController(ActionRequest $request, string $uuid): Order
    {
        /** @var array{amount: float|string|int, method: string, notes?: string|null} $validated */
        $validated = $request->validated();

        return $this->handle(
            $this->currentDashboardTenant($request),
            $uuid,
            $validated['amount'],
            $validated['method'],
            isset($validated['notes']) ? trim((string) $validated['notes']) : null,
        );
    }

    public function jsonResponse(Order $order): OrderResource
    {
        return (new OrderResource($order))
            ->additional([
                'message' => __('Payment recorded successfully.'),
            ]);
    }
}
