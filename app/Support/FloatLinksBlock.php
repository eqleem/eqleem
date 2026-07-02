<?php

namespace App\Support;

class FloatLinksBlock
{
    /**
     * @param  array<string, mixed>  $blockData
     * @return array{
     *     positionClass: string,
     *     showWhatsappButton: bool,
     *     whatsappUrl: ?string,
     *     showPhoneButton: bool,
     *     phoneNumber: string,
     *     showScrollTop: bool
     * }
     */
    public static function viewData(array $blockData): array
    {
        $showWhatsapp = (bool) ($blockData['show_whatsapp'] ?? true);
        $whatsappNumber = trim((string) ($blockData['whatsapp_number'] ?? ''));
        $showPhone = (bool) ($blockData['show_phone'] ?? false);
        $phoneNumber = trim((string) ($blockData['phone_number'] ?? ''));

        return [
            'positionClass' => ($blockData['position'] ?? 'bottom-end') === 'bottom-start' ? 'start-4' : 'end-4',
            'showWhatsappButton' => $showWhatsapp && $whatsappNumber !== '',
            'whatsappUrl' => $whatsappNumber !== '' ? 'https://wa.me/'.$whatsappNumber : null,
            'showPhoneButton' => $showPhone && $phoneNumber !== '',
            'phoneNumber' => $phoneNumber,
            'showScrollTop' => (bool) ($blockData['show_scroll_top'] ?? true),
        ];
    }
}
