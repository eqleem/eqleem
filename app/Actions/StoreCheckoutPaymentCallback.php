<?php

namespace App\Actions;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreCheckoutPaymentCallback
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

        $pendingCheckout = session('pending_store_checkout');

        if (! is_array($pendingCheckout)) {
            return $this->redirectWithError('انتهت جلسة الدفع. يرجى المحاولة مرة أخرى.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode(config('services.moyasar.secret_key')),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->get(config('services.moyasar.base_url').'payments/'.$validatedData['id'])->json();

        if (data_get($response, 'status') !== 'paid') {
            return $this->redirectWithError('عملية الدفع فشلت، الرجاء المحاولة مرة أخرى.');
        }

        $tenantId = currentTenantId();
        $expectedTenantId = (int) data_get($pendingCheckout, 'tenant_id');
        $expectedTotal = (int) data_get($pendingCheckout, 'grand_total');
        $paidAmount = (int) data_get($response, 'amount', 0);

        if (! $tenantId || $tenantId !== $expectedTenantId || $paidAmount !== $expectedTotal) {
            return $this->redirectWithError('تعذّر التحقق من عملية الدفع.');
        }

        $customer = (array) data_get($pendingCheckout, 'customer', []);
        $shippingMethod = (string) data_get($pendingCheckout, 'shipping_method', 'express');

        $order = app(CartService::class)->checkout(
            [
                'name' => (string) data_get($customer, 'name'),
                'phone' => (string) data_get($customer, 'phone'),
                'email' => data_get($customer, 'email'),
            ],
            $shippingMethod,
            'credit-card',
            [
                'gateway' => 'moyasar',
                'gateway_payment_id' => $validatedData['id'],
            ],
        );

        RecordOrderPayment::run($order, $paidAmount, 'credit-card', 'دفع إلكتروني عبر البطاقة');

        SendNewOrderNotificationEmail::run($order->fresh());

        session()->forget('pending_store_checkout');
        session()->flash('recent_order_id', $order->id);
        session()->flash('status', 'تم الدفع وإنشاء الطلب بنجاح.');

        return redirect()->route('tenant.pages.order-confirmation', [
            'tenant' => tenant('handle'),
            'order' => $order->uuid,
        ]);
    }

    protected function redirectWithError(string $message)
    {
        session()->flash('color', 'red');
        session()->flash('status', $message);

        return redirect()->route('tenant.pages.checkout');
    }
}
