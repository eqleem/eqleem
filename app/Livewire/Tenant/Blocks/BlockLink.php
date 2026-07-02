<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\RendersBlock;
use Livewire\Component;

class BlockLink extends Component
{
    use RendersBlock;

    protected function blockType(): string
    {
        return 'block-link';
    }
}
