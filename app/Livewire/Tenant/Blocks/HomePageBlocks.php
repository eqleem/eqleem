<?php

namespace App\Livewire\Tenant\Blocks;

use App\Models\Block;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Livewire\Component;

class HomePageBlocks extends Component
{
    public function placeholder(): string
    {
        return Blade::render(<<<'HTML'
            <div class="flex flex-col gap-y-3" aria-hidden="true">
                @for ($i = 0; $i < 3; $i++)
                    <div class="h-20 rounded-2xl bg-stone-100 animate-pulse"></div>
                @endfor
            </div>
        HTML);
    }

    public function render(): View
    {
        return view('livewire.tenant.blocks.home-page-blocks', [
            'pageBlocks' => Block::homePageBlocks(),
        ]);
    }
}
