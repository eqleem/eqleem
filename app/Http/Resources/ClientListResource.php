<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Lean payload for the dashboard clients table.
 *
 * @mixin Client
 */
class ClientListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Client $client */
        $client = $this->resource;

        return [
            'id' => $client->id,
            'uuid' => $client->uuid,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'active' => (bool) $client->active,
            'avatar' => $this->avatarUrl($client),
        ];
    }

    private function avatarUrl(Client $client): string
    {
        $metaAvatar = data_get($client->meta, 'avatar');

        if (filled($metaAvatar)) {
            if (str_starts_with((string) $metaAvatar, 'http')) {
                return (string) $metaAvatar;
            }

            return Storage::disk('public')->url((string) $metaAvatar);
        }

        return 'https://api.dicebear.com/9.x/fun-emoji/svg?seed='.$client->id;
    }
}
