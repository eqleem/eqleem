<?php

namespace App\Support;

use App\Models\Block;

class BlockLinkCard
{
    /**
     * @return array{
     *     title: string,
     *     url: string,
     *     icon: string,
     *     desc: string,
     *     brand_mark: array{type: string, value: string, color: string, url: string|null}|null
     * }|null
     */
    public static function fromBlock(?Block $block): ?array
    {
        if (! $block) {
            return null;
        }

        $data = $block->data ?? [];

        if ($block->type === 'link') {
            $url = $data['url'] ?? null;

            if (! filled($url)) {
                return null;
            }

            return [
                'title' => (string) ($data['title'] ?? $block->title ?? ''),
                'url' => (string) $url,
                'icon' => 'hugeicons:link-04',
                'desc' => '',
                'brand_mark' => BlockBrandMark::forDisplay(
                    is_array($data['brand_mark'] ?? null) ? $data['brand_mark'] : null
                ),
            ];
        }

        $url = CtaLink::urlFromData($data);

        if (! filled($url)) {
            return null;
        }

        return [
            'title' => CtaLink::titleFromData($data),
            'url' => $url,
            'icon' => CtaLink::iconFromData($data),
            'desc' => CtaLink::descriptionFromData($data),
            'brand_mark' => BlockBrandMark::forDisplay(
                is_array($data['brand_mark'] ?? null) ? $data['brand_mark'] : null
            ),
        ];
    }
}
