<?php

namespace App\Livewire\Tenant;

use App\Models\Block;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return tenantView('home', [
            'pageBlocks' => $this->pageBlocks(),
        ])->title('الرئيسية');
    }

    /**
     * @return Collection<int, Block>
     */
    protected function pageBlocks(): Collection
    {
        return Block::queryForTenantRoots()
            ->userBlocks()
            ->activeOnHome()
            ->orderBy('sort_order')
            ->get(['id', 'type', 'variant']);
    }
}
