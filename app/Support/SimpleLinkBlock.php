<?php

namespace App\Support;

use App\Models\Block;

class SimpleLinkBlock
{
    /**
     * @return array{title: string, url: string, icon: string, desc: string}|null
     */
    public static function card(?Block $block): ?array
    {
        if (! $block) {
            return null;
        }

        $data = $block->data ?? [];
        $url = $data['url'] ?? null;

        if (! filled($url)) {
            return null;
        }

        return [
            'title' => (string) ($data['title'] ?? $block->title ?? ''),
            'url' => (string) $url,
            'icon' => 'hugeicons:link-04',
            'desc' => '',
        ];
    }
}
