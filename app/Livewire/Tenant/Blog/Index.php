<?php

namespace App\Livewire\Tenant\Blog;

use Livewire\Component;

class Index extends Component
{
    //

    public function render()
    {
        return tenantView('blog.index')->title('المدونة');
    }
}
