<?php

namespace App\Support;

use App\Models\Content;

/**
 * Builds the view payload for FAQ-template content pages.
 */
class FaqPageView
{
    /**
     * @return array{
     *     page: Content,
     *     subtitle: string,
     *     faqs: list<array{id: string, question: string, answer: string}>
     * }
     */
    public static function make(Content $page): array
    {
        $defaults = Content::defaultFaqPageData();
        $data = is_array($page->data) ? $page->data : [];

        $faqs = collect(data_get($data, 'faqs', $defaults['faqs']))
            ->filter(fn ($faq): bool => is_array($faq))
            ->values()
            ->map(fn (array $faq): array => [
                'id' => (string) ($faq['id'] ?? ''),
                'question' => (string) ($faq['question'] ?? ''),
                'answer' => (string) ($faq['answer'] ?? ''),
            ])
            ->filter(fn (array $faq): bool => $faq['question'] !== '')
            ->values()
            ->all();

        return [
            'page' => $page,
            'subtitle' => (string) data_get($data, 'subtitle', $defaults['subtitle']),
            'faqs' => $faqs,
        ];
    }
}
