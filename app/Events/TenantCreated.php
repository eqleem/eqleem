<?php

namespace App\Events;

use App\Actions\CreateDefaultBlocks;
use App\Actions\SeedTenantDefaults;
use App\Models\Tenant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Tenant $tenant)
    {
        CreateDefaultBlocks::run($this->tenant);

        $this->tenant->theme_id = 1;
        $this->tenant->save();

        SeedTenantDefaults::run($this->tenant);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
