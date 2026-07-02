<?php

namespace App\Livewire\Tenant\Store;

use Livewire\Component;

class Detail extends Component
{
    //

    public function render()
    {
        return tenantView('store.detail')->title('تفاصيل المنتج');
    }
}
