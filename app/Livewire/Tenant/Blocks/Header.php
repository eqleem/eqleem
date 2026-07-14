<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Models\Tenant;
use App\Services\TenantProfileService;
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
        $tenant = tenant();
        $profile = app(TenantProfileService::class);
        $contact = $tenant ? $profile->contact($tenant) : [];

        $brandMark = $tenant
            ? $profile->brandMark($tenant)
            : ['type' => 'image', 'value' => '', 'color' => '', 'url' => ''];

        return $this->renderTenantBlockView($block, [
            'tenantName' => (string) ($tenant?->name ?? ''),
            'showAvatar' => (bool) ($data['show_avatar'] ?? true),
            'avatarUrl' => (string) ($brandMark['url'] ?? ''),
            'brandMark' => $brandMark,
            'showVerifiedBadge' => (bool) ($data['show_verified_badge'] ?? true),
            'bio' => (string) ($data['bio'] ?? ''),
            'location' => $this->locationLabel($contact),
            'socialLinks' => $this->socialLinks($tenant),
        ]);
    }

    /**
     * @param  array{country?: string, city?: string}  $contact
     */
    protected function locationLabel(array $contact): string
    {
        $parts = array_values(array_filter([
            $contact['city'] ?? null,
            $contact['country'] ?? null,
        ], fn (mixed $part): bool => filled($part)));

        return implode('، ', $parts);
    }

    /**
     * @return Collection<int, array{id: string, url: string, icon: string}>
     */
    protected function socialLinks(?Tenant $tenant): Collection
    {
        if (! $tenant) {
            return collect();
        }

        $networks = config('social-networks', []);

        return app(TenantProfileService::class)
            ->socialLinks($tenant)
            ->map(function (array $link) use ($networks): ?array {
                $network = $networks[$link['network'] ?? ''] ?? null;
                $url = (string) ($link['url'] ?? '');

                if (! $network || $url === '') {
                    return null;
                }

                return [
                    'id' => $link['id'],
                    'url' => $url,
                    'icon' => $network['icon'],
                ];
            })
            ->filter()
            ->values();
    }
}
