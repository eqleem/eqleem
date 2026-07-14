<?php

namespace App\Livewire\Tenant\Cart;

use App\Services\CartService;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;
use Livewire\Component;

class Badge extends Component
{
    public int $count = 0;

    public function placeholder(): string
    {
        return Blade::render(<<<'HTML'
            <div class="relative bg-black/10 backdrop-blur-md p-2 px-3 rounded-xl text-black/40 flex items-center gap-x-2 text-base" aria-hidden="true">
                <span class="inline-block size-6 rounded bg-black/10 animate-pulse"></span>
            </div>
        HTML);
    }

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
