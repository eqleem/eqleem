<?php

namespace App\Livewire\Tenant\Pages;

use App\Models\Client;
use App\Models\Setting;
use App\Services\CartService;
use App\Support\PaymentMethodRegistry;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class Checkout extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $shippingMethod = 'express';

    public ?string $paymentMethod = null;

    public string $bankTransferAccountId = '';

    public string $bankTransferReference = '';

    public string $paymentNote = '';

    public bool $creditCardReady = false;

    public function mount(): void
    {
        $client = authClient();

        if ($client instanceof Client) {
            $profile = $client->profileForTenant();

            $this->name = (string) ($profile['name'] ?? $client->name);
            $this->phone = (string) ($profile['phone'] ?? $client->phone ?? '');
            $this->email = (string) ($profile['email'] ?? $client->email ?? '');
        }

        $this->paymentMethod = $this->activePaymentMethods()->first()['slug'] ?? null;
    }

    public function updatedPaymentMethod(): void
    {
        $this->resetPaymentFields();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function customerRules(CartService $cart): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ];

        if ($cart->requiresShipping()) {
            $rules['shippingMethod'] = ['required', 'in:express,scheduled,pickup'];
        }

        return $rules;
    }

    protected function resolvedShippingMethod(CartService $cart): string
    {
        return $cart->requiresShipping() ? $this->shippingMethod : 'none';
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'paymentMethod.required' => 'يرجى اختيار وسيلة الدفع.',
            'paymentMethod.in' => 'وسيلة الدفع المختارة غير متاحة.',
            'bankTransferAccountId.required' => 'يرجى اختيار الحساب البنكي.',
            'bankTransferReference.required' => 'يرجى إدخال رقم الحوالة أو إيصال التحويل.',
            'paymentNote.required' => 'يرجى إدخال ملاحظة الدفع.',
        ];
    }

    public function placeFreeOrder(CartService $cart): void
    {
        $grandTotal = $this->validateCheckoutDetails($cart);

        if ($grandTotal > 0) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'يجب إتمام الدفع قبل تأكيد الطلب.',
            ]);
        }

        $this->completeCheckout($cart, 'free', [
            'payment_type' => 'free',
        ]);
    }

    public function confirmBankTransfer(CartService $cart): void
    {
        $this->assertSelectedPaymentMethod('bank-transfer');

        $this->validate([
            'bankTransferAccountId' => ['required', 'string'],
            'bankTransferReference' => ['required', 'string', 'max:120'],
        ]);

        $account = collect($this->selectedPaymentMethodSettings()['accounts'] ?? [])
            ->first(fn (array $row): bool => (string) data_get($row, 'id') === $this->bankTransferAccountId);

        if (! is_array($account)) {
            throw ValidationException::withMessages([
                'bankTransferAccountId' => 'الحساب البنكي المختار غير صالح.',
            ]);
        }

        $this->validateCheckoutDetails($cart);

        $this->completeCheckout($cart, 'bank-transfer', [
            'bank_transfer_account' => $account,
            'bank_transfer_reference' => trim($this->bankTransferReference),
        ]);
    }

    public function confirmCashOnDelivery(CartService $cart): void
    {
        $this->assertSelectedPaymentMethod('cash-on-delivery');
        $this->validateCheckoutDetails($cart);

        $this->completeCheckout($cart, 'cash-on-delivery');
    }

    public function confirmCustomPayment(CartService $cart): void
    {
        $this->assertSelectedPaymentMethod('custom');

        $this->validate([
            'paymentNote' => ['required', 'string', 'max:500'],
        ]);

        $this->validateCheckoutDetails($cart);

        $this->completeCheckout($cart, 'custom', [
            'payment_note' => trim($this->paymentNote),
        ]);
    }

    public function prepareCreditCardPayment(CartService $cart): void
    {
        $this->assertSelectedPaymentMethod('credit-card');

        if (blank(config('services.moyasar.publishable_key'))) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'بوابة الدفع غير مهيأة حالياً.',
            ]);
        }

        $grandTotal = $this->validateCheckoutDetails($cart);

        session([
            'pending_store_checkout' => [
                'tenant_id' => currentTenantId(),
                'customer' => [
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'email' => $this->email ?: null,
                ],
                'shipping_method' => $this->resolvedShippingMethod($cart),
                'payment_method' => 'credit-card',
                'grand_total' => $grandTotal,
            ],
        ]);

        $this->creditCardReady = true;
        $this->dispatch('init-store-moyasar', amount: $grandTotal);
    }

    protected function validateCheckoutDetails(CartService $cart): int
    {
        $this->validate($this->customerRules($cart));

        $shippingMethod = $this->resolvedShippingMethod($cart);
        $grandTotal = $cart->subtotal() + $cart->shippingFee($shippingMethod);

        if ($grandTotal <= 0) {
            return $grandTotal;
        }

        $activeSlugs = $this->activePaymentMethods()->pluck('slug')->all();

        $this->validate([
            'paymentMethod' => ['required', Rule::in($activeSlugs)],
        ]);

        $this->validatePaymentMethodLimits($grandTotal);

        return $grandTotal;
    }

    protected function validatePaymentMethodLimits(int $grandTotal): void
    {
        $settings = $this->selectedPaymentMethodSettings();
        $slug = (string) $this->paymentMethod;

        if ($slug === 'cash-on-delivery' && filled($settings['min_limit'] ?? null)) {
            $minimum = money_minor((float) $settings['min_limit']);

            if ($grandTotal < $minimum) {
                throw ValidationException::withMessages([
                    'paymentMethod' => 'الحد الأدنى للدفع عند الاستلام هو '.money_format_plain($minimum).'.',
                ]);
            }
        }

        if (in_array($slug, ['tabby', 'tamara'], true)) {
            if (filled($settings['min_limit'] ?? null) && $grandTotal < money_minor((float) $settings['min_limit'])) {
                throw ValidationException::withMessages([
                    'paymentMethod' => 'مبلغ الطلب أقل من الحد الأدنى لهذه الوسيلة.',
                ]);
            }

            if ($slug === 'tabby' && filled($settings['max_limit'] ?? null) && $grandTotal > money_minor((float) $settings['max_limit'])) {
                throw ValidationException::withMessages([
                    'paymentMethod' => 'مبلغ الطلب يتجاوز الحد الأقصى لهذه الوسيلة.',
                ]);
            }
        }
    }

    protected function assertSelectedPaymentMethod(string $slug): void
    {
        if ($this->paymentMethod !== $slug) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'يرجى اختيار وسيلة الدفع الصحيحة.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $paymentMeta
     */
    protected function completeCheckout(CartService $cart, string $paymentMethod, array $paymentMeta = []): void
    {
        $order = $cart->checkout(
            [
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email ?: null,
            ],
            $this->resolvedShippingMethod($cart),
            $paymentMethod,
            $paymentMeta,
        );

        $this->dispatch('cart-updated');
        session()->flash('recent_order_id', $order->id);

        $this->redirect(route('tenant.pages.order-confirmation', ['order' => $order->uuid]), navigate: true);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function activePaymentMethods(): Collection
    {
        return app(PaymentMethodRegistry::class)->activeForCheckout();
    }

    /**
     * @return array<string, mixed>
     */
    protected function selectedPaymentMethodSettings(): array
    {
        if (! filled($this->paymentMethod)) {
            return [];
        }

        return Setting::paymentMethod($this->paymentMethod);
    }

    protected function resetPaymentFields(): void
    {
        $this->reset([
            'bankTransferAccountId',
            'bankTransferReference',
            'paymentNote',
            'creditCardReady',
        ]);

        $this->resetValidation([
            'bankTransferAccountId',
            'bankTransferReference',
            'paymentNote',
        ]);
    }

    #[On('cart-updated')]
    #[On('client-authenticated')]
    public function refreshCheckout(): void
    {
        $this->mount();
    }

    public function render(CartService $cart)
    {
        $items = $cart->items();
        $subtotal = $cart->subtotal();
        $requiresShipping = $cart->requiresShipping();
        $shippingMethod = $this->resolvedShippingMethod($cart);
        $shippingFee = $cart->shippingFee($shippingMethod);
        $grandTotal = $subtotal + $shippingFee;
        $itemCount = $items->sum('quantity');
        $paymentMethods = $this->activePaymentMethods();
        $requiresPayment = $grandTotal > 0;
        $selectedPaymentMethod = $paymentMethods->firstWhere('slug', $this->paymentMethod);

        return tenantView('pages.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'requiresShipping' => $requiresShipping,
            'shippingFee' => $shippingFee,
            'grandTotal' => $grandTotal,
            'itemCount' => $itemCount,
            'paymentMethods' => $paymentMethods,
            'requiresPayment' => $requiresPayment,
            'selectedPaymentMethod' => $selectedPaymentMethod,
        ])->title('إتمام الشراء');
    }
}
