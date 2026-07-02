<?php

namespace App\Livewire\Tenant\Services;

use Livewire\Component;

class Index extends Component
{
    //

    public function render()
    {
        return tenantView('services.index')->title('خدماتنا');
    }
}
