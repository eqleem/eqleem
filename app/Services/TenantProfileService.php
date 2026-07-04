<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TenantProfileService
{
    /**
     * @return array{phone: string, email: string, whatsapp: string, country: string, city: string}
     */
    public function contact(Tenant $tenant): array
    {
        $this->ensureImported($tenant);

        return [
            'phone' => (string) data_get($tenant->meta, 'contact.phone', ''),
            'email' => (string) data_get($tenant->meta, 'contact.email', ''),
            'whatsapp' => (string) data_get($tenant->meta, 'contact.whatsapp', ''),
            'country' => (string) data_get($tenant->meta, 'contact.country', ''),
            'city' => (string) data_get($tenant->meta, 'contact.city', ''),
        ];
    }

    /**
     * @param  array{phone?: string, email?: string, whatsapp?: string, country?: string, city?: string}  $contact
     */
    public function saveContact(Tenant $tenant, array $contact): void
    {
        $existing = $this->contact($tenant);

        $tenant->meta->set('contact', [
            'phone' => (string) ($contact['phone'] ?? $existing['phone']),
            'email' => (string) ($contact['email'] ?? $existing['email']),
            'whatsapp' => (string) ($contact['whatsapp'] ?? $existing['whatsapp']),
            'country' => (string) ($contact['country'] ?? $existing['country']),
            'city' => (string) ($contact['city'] ?? $existing['city']),
        ]);
        $tenant->save();
    }

    /**
     * @return Collection<int, array{id: string, network: string, url: string, sort_order: int}>
     */
    public function socialLinks(Tenant $tenant): Collection
    {
        $this->ensureImported($tenant);

        return collect(data_get($tenant->meta, 'social_links', []))
            ->sortBy('sort_order')
            ->values();
    }

    public function addSocialLink(Tenant $tenant, string $network, string $url): void
    {
        $links = $this->socialLinks($tenant)->all();
        $maxOrder = (int) collect($links)->max('sort_order');

        $links[] = [
            'id' => (string) Str::uuid(),
            'network' => $network,
            'url' => $url,
            'sort_order' => $maxOrder + 1,
        ];

        $tenant->meta->set('social_links', $links);
        $tenant->save();
    }

    public function deleteSocialLink(Tenant $tenant, string $id): void
    {
        $links = $this->socialLinks($tenant)
            ->reject(fn (array $link): bool => $link['id'] === $id)
            ->values()
            ->all();

        $tenant->meta->set('social_links', $links);
        $tenant->save();
    }

    /**
     * @param  array<int, array{order: int, value: string}>  $items
     */
    public function updateSocialOrder(Tenant $tenant, array $items): void
    {
        $links = $this->socialLinks($tenant)->keyBy('id');

        foreach ($items as $item) {
            if ($links->has($item['value'])) {
                $link = $links->get($item['value']);
                $link['sort_order'] = $item['order'];
                $links->put($item['value'], $link);
            }
        }

        $tenant->meta->set('social_links', $links->sortBy('sort_order')->values()->all());
        $tenant->save();
    }

    protected function ensureImported(Tenant $tenant): void
    {
        if (filled(data_get($tenant->meta, 'contact')) || filled(data_get($tenant->meta, 'social_links'))) {
            return;
        }

        $this->importFromHeaderBlock($tenant);
    }

    protected function importFromHeaderBlock(Tenant $tenant): void
    {
        $headerBlock = Block::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', 'header')
            ->first();

        if (! $headerBlock) {
            return;
        }

        $data = $headerBlock->data ?? [];

        $tenant->meta->set('contact', [
            'phone' => '',
            'email' => '',
            'whatsapp' => '',
            'country' => (string) ($data['country'] ?? ''),
            'city' => (string) ($data['city'] ?? ''),
        ]);

        $links = Content::query()
            ->where('block_id', $headerBlock->id)
            ->type('social-link')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Content $link): array => [
                'id' => (string) $link->id,
                'network' => (string) ($link->data['network'] ?? ''),
                'url' => (string) ($link->data['url'] ?? ''),
                'sort_order' => (int) $link->sort_order,
            ])
            ->all();

        if ($links !== []) {
            $tenant->meta->set('social_links', $links);
        }

        $tenant->save();
    }
}
