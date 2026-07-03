<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Models\Block;
use App\Models\Content;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Header extends Component
{
    use ResolvesTenantBlockView;

    protected function blockType(): string
    {
        return 'header';
    }

    public function render(): View
    {
        $block = $this->resolveSingletonBlock();
        $data = $block?->data ?? [];

        return $this->renderTenantBlockView($block, [
            'tenantName' => (string) (tenant()?->name ?? ''),
            'showAvatar' => (bool) ($data['show_avatar'] ?? true),
            'avatarUrl' => (string) (tenant()?->logo ?? ''),
            'showVerifiedBadge' => (bool) ($data['show_verified_badge'] ?? true),
            'bio' => (string) ($data['bio'] ?? ''),
            'location' => $this->locationLabel($data),
            'socialLinks' => $this->socialLinks($block),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function locationLabel(array $data): string
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
    protected function socialLinks(?Block $block): Collection
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
