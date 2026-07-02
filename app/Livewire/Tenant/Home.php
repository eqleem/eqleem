<?php

namespace App\Livewire\Tenant;

use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return tenantView('home')->title('Home');
    }
}
