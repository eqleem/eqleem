<?php

namespace App\Livewire\Tenant\Pages;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class Cart extends Component
{
    public function updateQuantity(int $itemId, int $quantity, CartService $cart): void
    {
        $cart->updateQuantity($itemId, $quantity);
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $itemId, CartService $cart): void
    {
        $cart->removeItem($itemId);
        $this->dispatch('cart-updated');
    }

    #[On('cart-updated')]
    #[On('client-authenticated')]
    public function refreshCart(): void
    {
        //
    }

    public function render(CartService $cart)
    {
        $items = $cart->items();
        $subtotal = $cart->subtotal();
        $itemCount = $items->sum('quantity');

        return tenantView('pages.cart', [
            'items' => $items,
            'subtotal' => $subtotal,
            'itemCount' => $itemCount,
        ])->title('السلة');
    }
}
