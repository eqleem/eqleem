<?php

namespace App\Livewire\Tenant;

use App\Services\TenantProfileService;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        $tenant = tenant();
        $bio = $tenant
            ? app(TenantProfileService::class)->bio($tenant)
            : '';

        return tenantView('home')
            ->title('الرئيسية')
            ->layoutData([
                'metaDescription' => filled($bio) ? $bio : (string) tenant('name'),
            ]);
    }
}
