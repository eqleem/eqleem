<?php

namespace App\Livewire\Tenant\Portfolio;

use Livewire\Component;

class Detail extends Component
{
    //

    public function render()
    {
        return tenantView('portfolio.detail')->title('تفاصيل المشروع');
    }
}
