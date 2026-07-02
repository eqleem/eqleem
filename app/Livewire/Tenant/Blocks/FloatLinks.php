<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\RendersBlock;
use Livewire\Component;

class FloatLinks extends Component
{
    use RendersBlock;

    protected function blockType(): string
    {
        return 'float-links';
    }
}
