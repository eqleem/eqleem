<div class="flex flex-col gap-y-3">
    @foreach ($pageBlocks as $block)
        <x-tenant-page-block :block="$block" wire:key="home-page-block-{{ $block->id }}" />
    @endforeach
</div>
