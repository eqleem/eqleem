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
        $tenantId = currentTenantId();

        return Block::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereNull('parent_id')
            ->where('is_default', false)
            ->where('active', true)
            ->where('position', 'home')
            ->orderBy('sort_order')
            ->get();
    }
}
