<?php

namespace App\API\Settings\Concerns;

use App\Models\Tenant;
use App\Services\TenantProfileService;

/**
 * Shared payload builder for general-info settings responses.
 */
trait BuildsGeneralInfoSettings
{
    /**
     * @return array<string, mixed>
     */
    protected function generalInfoPayload(Tenant $tenant): array
    {
        $profile = app(TenantProfileService::class);
        $tenant = $tenant->fresh() ?? $tenant;

        return [
            'name' => (string) ($tenant->name ?? ''),
            'logo' => $profile->logo($tenant),
            'brand_mark' => $profile->brandMark($tenant),
            'contact' => $profile->contact($tenant),
            'social_links' => $profile->socialLinks($tenant)->values()->all(),
            'social_networks' => collect(config('social-networks', []))
                ->map(fn (array $network): string => $network['label'])
                ->all(),
        ];
    }
}
