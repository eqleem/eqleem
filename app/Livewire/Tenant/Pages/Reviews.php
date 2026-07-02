<?php

namespace App\Livewire\Tenant\Pages;

use Livewire\Component;

class Reviews extends Component
{
    //

    public function render()
    {
        return tenantView('pages.reviews')->title('التقييمات');
    }
}
