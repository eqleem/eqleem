<?php

namespace App\Livewire\Tenant;

use App\Support\TenantPageBlocks;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        $bio = (string) data_get(
            app(TenantPageBlocks::class)->singleton('header')?->data,
            'bio',
            ''
        );

        return tenantView('home')
            ->title('الرئيسية')
            ->layoutData([
                'metaDescription' => filled($bio) ? $bio : (string) tenant('name'),
            ]);
    }
}
