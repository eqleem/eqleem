<?php

namespace App\Listeners;

use App\Actions\CreateDefaultBlocks;
use App\Events\TenantCreated;

class CreateDefaultBlocksForTenant
{
    public function handle(TenantCreated $event): void
    {
        CreateDefaultBlocks::run($event->tenant);
    }
}
