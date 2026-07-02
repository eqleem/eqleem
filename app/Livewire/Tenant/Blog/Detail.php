<?php

namespace App\Livewire\Tenant\Blog;

use Livewire\Component;

class Detail extends Component
{
    //

    public function render()
    {
        return tenantView('blog.detail')->title('تفاصيل المقال');
    }
}
