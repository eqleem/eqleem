<?php

namespace App\Livewire\Tenant\Pages;

use Livewire\Component;

class Cart extends Component
{
    //

    public function render()
    {
        return tenantView('pages.cart')->title('السلة');
    }
}
