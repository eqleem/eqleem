<?php

namespace App\API\Payments;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowPayment
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, string $uuid): Payment
    {
        setCurrentTenant($tenant);

        $payment = Payment::query()
            ->forTenant()
            ->with([
                'user:id,name,email',
                'client:id,name,email,phone',
                'order:id,uuid,number',
            ])
            ->where('tenant_id', $tenant->id)
            ->where('uuid', $uuid)
            ->first();

        if (! $payment instanceof Payment) {
            throw (new ModelNotFoundException)->setModel(Payment::class, [$uuid]);
        }

        return $payment;
    }

    public function asController(ActionRequest $request, string $uuid): Payment
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Payment $payment): PaymentResource
    {
        return new PaymentResource($payment);
    }
}
