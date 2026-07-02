<?php

namespace App\Livewire\Tenant\Pages;

use Livewire\Component;

class Checkout extends Component
{
    //

    public function render()
    {
        return tenantView('pages.checkout')->title('إتمام الشراء');
    }
}
