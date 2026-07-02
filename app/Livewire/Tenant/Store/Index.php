<?php

namespace App\Livewire\Tenant\Store;

use Livewire\Component;

class Index extends Component
{
    //

    public function render()
    {
        return tenantView('store.index')->title('المتجر');
    }
}
