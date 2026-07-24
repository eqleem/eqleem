<?php

namespace App\Actions;

use App\Models\Payment;
use App\Models\Plan;
use App\Support\Moyasar;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class PaymentCallback
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return ['id' => 'required|string'];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function verifyAndSubscribe(string $paymentId): array
    {
        $response = Moyasar::fetchPayment($paymentId);

        if (! Moyasar::isPaid($response)) {
            return [
                'success' => false,
                'message' => 'عملية الدفع فشلت، الرجاء المحاولة مرة أخرى',
            ];
        }

        $planId = data_get($response, 'metadata.plan_id');
        $tenant = currentTenant();

        if (! $tenant || ! $planId) {
            return [
                'success' => false,
                'message' => 'تعذّر إتمام الاشتراك، حاول مرة أخرى.',
            ];
        }

        Payment::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id(),
            'purchased_id' => $planId,
            'purchased_type' => Plan::class,
            'amount' => (int) data_get($response, 'amount', 0),
            'payment_id' => $paymentId,
            'gateway' => 'moyasar',
            'initial_status' => data_get($response, 'status'),
            'currency' => data_get($response, 'currency', money_currency()),
            'description' => data_get($response, 'description'),
            'meta' => $response,
            'reason' => 'tenant-subscribe-to-plan',
        ]);

        $plan = Plan::query()->whereKey($planId)->where('is_system', true)->firstOrFail();
        SubscribeTenantToPlan::run($tenant, $plan);

        return [
            'success' => true,
            'message' => 'تم تفعيل الباقة بنجاح.',
        ];
    }

    public function handle(Request $request, ?string $successRedirect = null, ?string $failureRedirect = null)
    {
        $this->fill($request->all());
        $validatedData = $this->validateAttributes();

        $successRedirect ??= route('admin.plan.home');
        $failureRedirect ??= route('admin.plan.home');

        $result = $this->verifyAndSubscribe($validatedData['id']);

        if (! $result['success']) {
            session()->flash('color', 'red');
            session()->flash('status', $result['message']);

            return redirect($failureRedirect);
        }

        session()->flash('status', $result['message']);

        return redirect($successRedirect);
    }
}
