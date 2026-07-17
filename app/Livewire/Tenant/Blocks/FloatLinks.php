<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Services\TenantProfileService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FloatLinks extends Component
{
    use ResolvesTenantBlockView;

    public $width;

    protected function blockType(): string
    {
        return 'float-links';
    }

    public function render(): View
    {
        $block = $this->resolveSingletonBlock();
        $blockData = $block?->data ?? [];
        $showWhatsapp = (bool) ($blockData['show_whatsapp'] ?? true);
        $showPhone = (bool) ($blockData['show_phone'] ?? false);

        $tenant = tenant();
        $contact = $tenant
            ? app(TenantProfileService::class)->contact($tenant)
            : ['phone' => '', 'whatsapp' => ''];

        $whatsappNumber = trim((string) ($contact['whatsapp'] ?? ''));
        $phoneNumber = trim((string) ($contact['phone'] ?? ''));

        return $this->renderTenantBlockView($block, [
            'positionClass' => ($blockData['position'] ?? 'bottom-end') === 'bottom-start' ? 'start-4' : 'end-4',
            'showWhatsappButton' => $showWhatsapp && $whatsappNumber !== '',
            'whatsappUrl' => $whatsappNumber !== '' ? 'https://wa.me/'.preg_replace('/\D+/', '', $whatsappNumber) : null,
            'showPhoneButton' => $showPhone && $phoneNumber !== '',
            'phoneNumber' => $phoneNumber,
        ]);
    }
}
