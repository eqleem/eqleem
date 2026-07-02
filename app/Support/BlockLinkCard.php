<?php

namespace App\Support;

use App\Models\Block;

class BlockLinkCard
{
    /**
     * @return array{title: string, url: string, icon: string, desc: string}|null
     */
    public static function fromBlock(?Block $block): ?array
    {
        if (! $block) {
            return null;
        }

        $data = $block->data ?? [];
        $url = CtaLink::urlFromData($data);

        if (! filled($url)) {
            return null;
        }

        return [
            'title' => CtaLink::titleFromData($data),
            'url' => $url,
            'icon' => CtaLink::iconFromData($data),
            'desc' => CtaLink::descriptionFromData($data),
        ];
    }
}
