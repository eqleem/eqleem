<?php

namespace App\Livewire\Tenant\Cart;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class Badge extends Component
{
    public int $count = 0;

    public function mount(CartService $cart): void
    {
        $this->count = $cart->itemCount();
    }

    #[On('cart-updated')]
    #[On('client-authenticated')]
    public function refreshCount(CartService $cart): void
    {
        $this->count = $cart->itemCount();
    }

    public function render()
    {
        return view('livewire.tenant.cart.badge');
    }
}
