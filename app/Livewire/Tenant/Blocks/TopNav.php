<?php

namespace App\Livewire\Tenant\Blocks;

use Livewire\Component;
use App\Models\Block;

class TopNav extends Component
{
    public function render()
    {
        $block = Block::where('type', 'top-nav')->first();

        return view()
            ->first([
                $block->variant, 
                'tenant-theme::blocks.top-nav', 
                'default-tenant-theme::blocks.top-nav'
            ], ['block' => $block]);
    }
}
