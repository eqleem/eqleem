<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FloatLinks extends Component
{
    use ResolvesTenantBlockView;

    protected function blockType(): string
    {
        return 'float-links';
    }

    public function render(): View
    {
        $block = $this->resolveSingletonBlock();
        $blockData = $block?->data ?? [];
        $showWhatsapp = (bool) ($blockData['show_whatsapp'] ?? true);
        $whatsappNumber = trim((string) ($blockData['whatsapp_number'] ?? ''));
        $showPhone = (bool) ($blockData['show_phone'] ?? false);
        $phoneNumber = trim((string) ($blockData['phone_number'] ?? ''));

        return $this->renderTenantBlockView($block, [
            'positionClass' => ($blockData['position'] ?? 'bottom-end') === 'bottom-start' ? 'start-4' : 'end-4',
            'showWhatsappButton' => $showWhatsapp && $whatsappNumber !== '',
            'whatsappUrl' => $whatsappNumber !== '' ? 'https://wa.me/'.$whatsappNumber : null,
            'showPhoneButton' => $showPhone && $phoneNumber !== '',
            'phoneNumber' => $phoneNumber,
        ]);
    }
}
