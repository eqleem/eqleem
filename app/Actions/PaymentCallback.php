<?php

namespace App\Actions;

use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class PaymentCallback
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return ['id' => 'required|string'];
    }

    public function handle(Request $request)
    {
        $this->fill($request->all());
        $validatedData = $this->validateAttributes();

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode(config('services.moyasar.secret_key')),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->get(config('services.moyasar.base_url').'payments/'.$validatedData['id'])->json();

        if (data_get($response, 'status') !== 'paid') {
            session()->flash('color', 'red');
            session()->flash('status', 'عملية الدفع فشلت، الرجاء المحاولة مرة أخرى');

            return redirect()->route('admin.plan.home');
        }

        $planId = data_get($response, 'metadata.plan_id');
        $tenant = currentTenant();

        if (! $tenant || ! $planId) {
            session()->flash('color', 'red');
            session()->flash('status', 'تعذّر إتمام الاشتراك، حاول مرة أخرى.');

            return redirect()->route('admin.plan.home');
        }

        Payment::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id(),
            'purchased_id' => $planId,
            'purchased_type' => Plan::class,
            'amount' => (int) data_get($response, 'amount', 0),
            'payment_id' => $validatedData['id'],
            'gateway' => 'moyasar',
            'initial_status' => data_get($response, 'status'),
            'currency' => data_get($response, 'currency', money_currency()),
            'description' => data_get($response, 'description'),
            'meta' => $response,
            'reason' => 'tenant-subscribe-to-plan',
        ]);

        $this->subscribeTo($planId);

        session()->flash('status', 'تم تفعيل الباقة بنجاح.');

        return redirect()->route('admin.plan.home');
    }

    protected function subscribeTo(int|string $planId): void
    {
        $plan = Plan::query()->whereKey($planId)->where('is_system', true)->firstOrFail();
        $tenant = currentTenant();

        SubscribeTenantToPlan::run($tenant, $plan);
    }
}
