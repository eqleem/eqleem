<?php

namespace App\Actions;

use App\Services\CartService;
use App\Support\Moyasar;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreCheckoutPaymentCallback
{
    use AsAction, WithAttributes;

    public function __construct(protected CartService $cart) {}

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

        $response = Moyasar::fetchPayment($validatedData['id']);

        if (! Moyasar::isPaid($response)) {
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

        $order = $this->cart->checkout(
            [
                'name' => (string) data_get($customer, 'name'),
                'phone' => (string) data_get($customer, 'phone'),
                'email' => data_get($customer, 'email'),
                'address' => data_get($customer, 'address'),
                'country' => data_get($customer, 'country'),
                'city_id' => data_get($customer, 'city_id'),
                'neighborhood' => data_get($customer, 'neighborhood'),
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
