<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\RendersBlock;
use Livewire\Component;

class Header extends Component
{
    use RendersBlock;

    protected function blockType(): string
    {
        return 'header';
    }
}
