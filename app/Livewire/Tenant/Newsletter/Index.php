<?php

namespace App\Livewire\Tenant\Newsletter;

use Livewire\Component;

class Index extends Component
{
    //

    public function render()
    {
        return tenantView('newsletter.index')->title('النشرة البريدية');
    }
}
