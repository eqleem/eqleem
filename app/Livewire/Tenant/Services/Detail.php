<?php

namespace App\Livewire\Tenant\Services;

use Livewire\Component;

class Detail extends Component
{
    //

    public function render()
    {
        return tenantView('services.detail')->title('تفاصيل الخدمة');
    }
}
