<?php

namespace App\Support;

use App\Models\Block;
use App\Models\Content;
use Illuminate\Support\Collection;

class HeaderBlock
{
    /**
     * @return array{
     *     tenantName: string,
     *     showAvatar: bool,
     *     avatarUrl: string,
     *     showVerifiedBadge: bool,
     *     bio: string,
     *     location: string,
     *     socialLinks: Collection<int, array{id: int, url: string, icon: string}>
     * }
     */
    public static function viewData(?Block $block): array
    {
        $data = $block?->data ?? [];

        return [
            'tenantName' => (string) (tenant()?->name ?? ''),
            'showAvatar' => (bool) ($data['show_avatar'] ?? true),
            'avatarUrl' => (string) (tenant()?->logo ?? ''),
            'showVerifiedBadge' => (bool) ($data['show_verified_badge'] ?? true),
            'bio' => (string) ($data['bio'] ?? ''),
            'location' => self::locationLabel($data),
            'socialLinks' => self::socialLinks($block),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function locationLabel(array $data): string
    {
        $parts = array_values(array_filter([
            $data['city'] ?? null,
            $data['country'] ?? null,
        ], fn (mixed $part): bool => filled($part)));

        return implode('، ', $parts);
    }

    /**
     * @return Collection<int, array{id: int, url: string, icon: string}>
     */
    protected static function socialLinks(?Block $block): Collection
    {
        if (! $block) {
            return collect();
        }

        $networks = config('social-networks', []);

        return $block->activeContents('social-link')
            ->map(function (Content $link) use ($networks): ?array {
                $network = $networks[$link->data['network'] ?? ''] ?? null;
                $url = (string) ($link->data['url'] ?? '');

                if (! $network || $url === '') {
                    return null;
                }

                return [
                    'id' => $link->id,
                    'url' => $url,
                    'icon' => $network['icon'],
                ];
            })
            ->filter()
            ->values();
    }
}
