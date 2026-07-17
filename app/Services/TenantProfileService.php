<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\BlockBrandMark;
use App\Support\TablerIconsCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantProfileService
{
    public function bio(Tenant $tenant): string
    {
        $this->ensureBioImported($tenant);

        return (string) data_get($tenant->meta, 'bio', '');
    }

    public function saveBio(Tenant $tenant, string $bio): void
    {
        $tenant->meta->set('bio', $bio);
        $tenant->meta->set('bio_saved', true);
        $tenant->save();
    }

    /**
     * @return array{phone: string, email: string, whatsapp: string, country: string, city: string}
     */
    public function contact(Tenant $tenant): array
    {
        $this->ensureImported($tenant);

        // Public reads use tenant meta only — never load the owner User here.
        // Owner defaults are copied once via seedContactFromUser() / import.
        return [
            'phone' => (string) data_get($tenant->meta, 'contact.phone', $tenant->phone ?? ''),
            'email' => (string) data_get($tenant->meta, 'contact.email', $tenant->email ?? ''),
            'whatsapp' => (string) data_get($tenant->meta, 'contact.whatsapp', ''),
            'country' => (string) data_get($tenant->meta, 'contact.country', ''),
            'city' => (string) data_get($tenant->meta, 'contact.city', ''),
        ];
    }

    public function seedContactFromUser(Tenant $tenant): void
    {
        if ((bool) data_get($tenant->meta, 'contact_saved')) {
            return;
        }

        $tenant->loadMissing('user');

        if (! $tenant->user) {
            return;
        }

        $defaults = $this->userContactDefaults($tenant->user);
        $existing = data_get($tenant->meta, 'contact', []);
        $existing = is_array($existing) ? $existing : [];

        $tenant->meta->set('contact', [
            'phone' => filled($existing['phone'] ?? null) ? (string) $existing['phone'] : $defaults['phone'],
            'email' => filled($existing['email'] ?? null) ? (string) $existing['email'] : $defaults['email'],
            'whatsapp' => (string) ($existing['whatsapp'] ?? ''),
            'country' => (string) ($existing['country'] ?? ''),
            'city' => (string) ($existing['city'] ?? ''),
        ]);

        if (filled($defaults['email']) && ! filled($tenant->email)) {
            $tenant->email = $defaults['email'];
        }

        if (filled($defaults['phone']) && ! filled($tenant->phone)) {
            $tenant->phone = $defaults['phone'];
        }

        $tenant->save();
    }

    public function seedFromUser(Tenant $tenant): void
    {
        $this->seedContactFromUser($tenant);
        $this->seedLogoFromUser($tenant);
    }

    public function seedLogoFromUser(Tenant $tenant): void
    {
        if ((bool) data_get($tenant->meta, 'logo_saved')) {
            return;
        }

        if (filled(data_get($tenant->meta, 'logo')) || filled(data_get($tenant->data, 'logo'))) {
            return;
        }

        $tenant->loadMissing('user');
        $logo = $this->userLogoDefault($tenant->user);

        if (! filled($logo)) {
            return;
        }

        $tenant->meta->set('logo', $logo);
        $tenant->save();
    }

    public function logo(Tenant $tenant): string
    {
        $mark = $this->brandMark($tenant);

        if (in_array($mark['type'], ['emoji', 'icon'], true)) {
            return $this->brandMarkDataUri($mark);
        }

        $stored = data_get($tenant->meta, 'logo') ?? data_get($tenant->data, 'logo');

        if (Str::startsWith((string) $stored, 'http')) {
            return (string) $stored;
        }

        $path = is_array($stored) ? ($stored['path'] ?? null) : $stored;

        if (filled($path)) {
            return Storage::url((string) $path);
        }

        return 'https://api.dicebear.com/9.x/shapes/svg?seed='.$tenant->uuid;
    }

    /**
     * @return array{type: string, value: string, color: string, url: string|null}
     */
    public function brandMark(Tenant $tenant): array
    {
        $stored = data_get($tenant->meta, 'brand_mark');

        if (is_array($stored)) {
            $type = (string) ($stored['type'] ?? '');

            if (in_array($type, ['emoji', 'icon'], true) && filled($stored['value'] ?? null)) {
                return [
                    'type' => $type,
                    'value' => (string) $stored['value'],
                    'color' => $this->normalizeBrandMarkColor($stored['color'] ?? null),
                    'url' => null,
                ];
            }

            if ($type === 'image') {
                $url = $this->imageLogoUrl($tenant);

                return [
                    'type' => 'image',
                    'value' => '',
                    'color' => '',
                    'url' => $url !== '' ? $url : null,
                ];
            }
        }

        $url = $this->imageLogoUrl($tenant);

        if ($url !== '') {
            return [
                'type' => 'image',
                'value' => '',
                'color' => '',
                'url' => $url,
            ];
        }

        return [
            'type' => 'image',
            'value' => '',
            'color' => '',
            'url' => 'https://api.dicebear.com/9.x/shapes/svg?seed='.$tenant->uuid,
        ];
    }

    public function hasLogo(Tenant $tenant): bool
    {
        $mark = data_get($tenant->meta, 'brand_mark');

        if (is_array($mark) && in_array(($mark['type'] ?? null), ['emoji', 'icon'], true) && filled($mark['value'] ?? null)) {
            return true;
        }

        $stored = data_get($tenant->meta, 'logo') ?? data_get($tenant->data, 'logo');

        return filled($stored);
    }

    public function saveLogo(Tenant $tenant, string $path): void
    {
        $tenant->meta->set('logo', $path);
        $tenant->meta->set('logo_saved', true);
        $tenant->meta->set('brand_mark', [
            'type' => 'image',
            'value' => '',
            'color' => '',
        ]);
        $tenant->save();
    }

    /**
     * @param  array{type: string, value?: string|null, color?: string|null}  $mark
     */
    public function saveBrandMark(Tenant $tenant, array $mark): void
    {
        $type = (string) ($mark['type'] ?? '');

        if ($type === 'emoji') {
            $value = trim((string) ($mark['value'] ?? ''));

            if ($value === '') {
                return;
            }

            $tenant->meta->set('brand_mark', [
                'type' => 'emoji',
                'value' => mb_substr($value, 0, 16),
                'color' => '',
            ]);
            $tenant->meta->set('logo_saved', true);
            $tenant->save();

            return;
        }

        if ($type === 'icon') {
            $iconId = app(TablerIconsCatalog::class)->normalizeId((string) ($mark['value'] ?? ''));

            if ($iconId === null) {
                return;
            }

            $tenant->meta->set('brand_mark', [
                'type' => 'icon',
                'value' => $iconId,
                'color' => $this->normalizeBrandMarkColor($mark['color'] ?? null),
            ]);
            $tenant->meta->set('logo_saved', true);
            $tenant->save();
        }
    }

    public function clearBrandMark(Tenant $tenant): void
    {
        $tenant->meta->set('brand_mark', null);
        $tenant->meta->set('logo', null);
        $tenant->meta->set('logo_saved', true);
        $tenant->save();
    }

    protected function imageLogoUrl(Tenant $tenant): string
    {
        $stored = data_get($tenant->meta, 'logo') ?? data_get($tenant->data, 'logo');

        if (Str::startsWith((string) $stored, 'http')) {
            return (string) $stored;
        }

        $path = is_array($stored) ? ($stored['path'] ?? null) : $stored;

        if (filled($path)) {
            return Storage::url((string) $path);
        }

        return '';
    }

    protected function normalizeBrandMarkColor(mixed $color): string
    {
        return BlockBrandMark::normalizeColor($color);
    }

    /**
     * @param  array{type: string, value: string, color: string, url: string|null}  $mark
     */
    protected function brandMarkDataUri(array $mark): string
    {
        if ($mark['type'] === 'emoji') {
            $emoji = htmlspecialchars($mark['value'], ENT_QUOTES | ENT_XML1, 'UTF-8');
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
                .'<text x="32" y="42" text-anchor="middle" font-size="40">'.$emoji.'</text>'
                .'</svg>';

            return 'data:image/svg+xml;charset=utf-8,'.rawurlencode($svg);
        }

        $color = htmlspecialchars($mark['color'] !== '' ? $mark['color'] : '#e5e7eb', ENT_QUOTES | ENT_XML1, 'UTF-8');
        $label = htmlspecialchars(str_replace('tabler:', '', $mark['value']), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" rx="12" fill="#111827"/>'
            .'<text x="32" y="38" text-anchor="middle" font-size="10" fill="'.$color.'">'.$label.'</text>'
            .'</svg>';

        return 'data:image/svg+xml;charset=utf-8,'.rawurlencode($svg);
    }

    /**
     * @param  array{phone?: string, email?: string, whatsapp?: string, country?: string, city?: string}  $contact
     */
    public function saveContact(Tenant $tenant, array $contact): void
    {
        $existing = $this->contact($tenant);

        $merged = [
            'phone' => (string) ($contact['phone'] ?? $existing['phone']),
            'email' => (string) ($contact['email'] ?? $existing['email']),
            'whatsapp' => (string) ($contact['whatsapp'] ?? $existing['whatsapp']),
            'country' => (string) ($contact['country'] ?? $existing['country']),
            'city' => (string) ($contact['city'] ?? $existing['city']),
        ];

        $tenant->meta->set('contact', $merged);
        $tenant->meta->set('contact_saved', true);

        // Keep tenants.phone / tenants.email columns in sync with meta.contact.
        $tenant->phone = $merged['phone'] !== '' ? $merged['phone'] : $tenant->phone;
        $tenant->email = $merged['email'] !== '' ? $merged['email'] : $tenant->email;

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
        $this->ensureBioImported($tenant);

        if (! filled(data_get($tenant->meta, 'contact')) && ! filled(data_get($tenant->meta, 'social_links'))) {
            $this->importFromHeaderBlock($tenant);
        }

        $this->ensureFloatLinksContactImported($tenant);
    }

    protected function ensureBioImported(Tenant $tenant): void
    {
        if ((bool) data_get($tenant->meta, 'bio_saved')) {
            return;
        }

        $headerBlock = Block::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', 'header')
            ->first();

        $headerBio = (string) data_get($headerBlock?->data ?? [], 'bio', '');

        $tenant->meta->set('bio', $headerBio);
        $tenant->meta->set('bio_saved', true);
        $tenant->save();
    }

    /**
     * Migrate legacy float-links phone/whatsapp into tenants.meta.contact once.
     */
    protected function ensureFloatLinksContactImported(Tenant $tenant): void
    {
        if ((bool) data_get($tenant->meta, 'float_links_contact_imported')) {
            return;
        }

        $floatLinks = Block::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', 'float-links')
            ->first();

        $data = $floatLinks?->data ?? [];
        $legacyPhone = trim((string) ($data['phone_number'] ?? ''));
        $legacyWhatsapp = trim((string) ($data['whatsapp_number'] ?? ''));

        if ($legacyPhone !== '' || $legacyWhatsapp !== '') {
            $existing = data_get($tenant->meta, 'contact', []);
            $existing = is_array($existing) ? $existing : [];

            $tenant->meta->set('contact', [
                'phone' => filled($existing['phone'] ?? null) ? (string) $existing['phone'] : $legacyPhone,
                'email' => (string) ($existing['email'] ?? ''),
                'whatsapp' => filled($existing['whatsapp'] ?? null) ? (string) $existing['whatsapp'] : $legacyWhatsapp,
                'country' => (string) ($existing['country'] ?? ''),
                'city' => (string) ($existing['city'] ?? ''),
            ]);

            if ($legacyPhone !== '' && ! filled($tenant->phone)) {
                $tenant->phone = $legacyPhone;
            }
        }

        $tenant->meta->set('float_links_contact_imported', true);
        $tenant->save();
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

        if (! (bool) data_get($tenant->meta, 'bio_saved')) {
            $tenant->meta->set('bio', (string) ($data['bio'] ?? ''));
            $tenant->meta->set('bio_saved', true);
        }

        $tenant->meta->set('contact', [
            'phone' => '',
            'email' => '',
            'whatsapp' => '',
            'country' => (string) ($data['country'] ?? ''),
            'city' => (string) ($data['city'] ?? ''),
        ]);

        $this->seedContactFromUser($tenant);

        $this->seedLogoFromUser($tenant);

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

    /**
     * @return array{phone: string, email: string}
     */
    protected function userContactDefaults(?User $user): array
    {
        if (! $user) {
            return [
                'phone' => '',
                'email' => '',
            ];
        }

        return [
            'phone' => (string) ($user->phone ?? ''),
            'email' => (string) ($user->email ?? ''),
        ];
    }

    protected function userLogoDefault(?User $user): string
    {
        if (! $user) {
            return '';
        }

        $image = (string) ($user->getRawOriginal('image') ?? '');

        if (! filled($image)) {
            return '';
        }

        if (Str::startsWith($image, 'http')) {
            return $image;
        }

        return Storage::url($image);
    }
}
