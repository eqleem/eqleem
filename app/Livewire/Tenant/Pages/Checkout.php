<?php

namespace App\Livewire\Tenant\Pages;

use App\Models\Client;
use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class Checkout extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $shippingMethod = 'express';

    public string $paymentMethod = 'card';

    public function mount(): void
    {
        $client = authClient();

        if (! $client instanceof Client) {
            return;
        }

        $profile = $client->profileForTenant();

        $this->name = (string) ($profile['name'] ?? $client->name);
        $this->phone = (string) ($profile['phone'] ?? $client->phone ?? '');
        $this->email = (string) ($profile['email'] ?? $client->email ?? '');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'shippingMethod' => ['required', 'in:express,scheduled,pickup'],
            'paymentMethod' => ['required', 'in:card,apple_pay,bank_transfer,cod'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
        ];
    }

    public function placeOrder(CartService $cart): void
    {
        $this->validate();

        $order = $cart->checkout(
            [
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email ?: null,
            ],
            $this->shippingMethod,
            $this->paymentMethod,
        );

        $this->dispatch('cart-updated');
        session()->flash('order_placed', $order->number);

        $this->redirect(route('tenant.store.index'), navigate: true);
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
        $shippingFee = $cart->shippingFee($this->shippingMethod);
        $grandTotal = $subtotal + $shippingFee;
        $itemCount = $items->sum('quantity');

        return tenantView('pages.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'grandTotal' => $grandTotal,
            'itemCount' => $itemCount,
        ])->title('إتمام الشراء');
    }
}
