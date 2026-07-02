<?php

namespace App\Livewire\Tenant\Portfolio;

use Livewire\Component;

class Index extends Component
{
    //

    public function render()
    {
        return tenantView('portfolio.index')->title('أعمالنا');
    }
}
