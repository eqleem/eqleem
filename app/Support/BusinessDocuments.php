<?php

namespace App\Support;

use Illuminate\Support\Collection;

class BusinessDocuments
{
    /**
     * @return array<string, array{label: string, logo: string|null}>
     */
    public static function definitions(): array
    {
        return config('business-documents.documents', []);
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return collect(self::definitions())
            ->mapWithKeys(fn (array $document, string $key): array => [
                $key => (string) ($document['label'] ?? $key),
            ])
            ->all();
    }

    public static function showsDocumentsWarranties(array $blockData): bool
    {
        return (bool) ($blockData['show_documents_warranties'] ?? true);
    }

    /**
     * @param  array<string, mixed>  $blockData
     * @return array<string, string>
     */
    public static function numbersFromBlockData(array $blockData): array
    {
        $stored = is_array($blockData['document_numbers'] ?? null)
            ? $blockData['document_numbers']
            : [];

        $numbers = [];

        foreach (array_keys(self::definitions()) as $key) {
            $numbers[$key] = (string) ($stored[$key] ?? '');
        }

        return $numbers;
    }

    /**
     * @param  array<string, string>  $numbers
     * @return array<string, string>
     */
    public static function sanitizeNumbers(array $numbers): array
    {
        return collect($numbers)
            ->map(fn (mixed $number): string => trim((string) $number))
            ->filter(fn (string $number): bool => $number !== '')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $blockData
     * @return list<array<string, mixed>>
     */
    public static function documentsForStorage(array $blockData): array
    {
        if (array_key_exists('documents', $blockData)) {
            $documents = is_array($blockData['documents']) ? $blockData['documents'] : [];

            return collect($documents)
                ->values()
                ->map(fn (mixed $document, int $index): ?array => is_array($document)
                    ? self::normalizeStoredDocument($document, $index)
                    : null)
                ->filter()
                ->values()
                ->all();
        }

        $numbers = self::numbersFromBlockData($blockData);

        return collect(self::definitions())
            ->except('other')
            ->map(function (array $definition, string $type) use ($numbers): ?array {
                $value = trim((string) ($numbers[$type] ?? ''));

                if ($value === '') {
                    return null;
                }

                return [
                    'id' => 'legacy-'.$type,
                    'type' => $type,
                    'custom_label' => '',
                    'value' => $value,
                    'brand_mark' => null,
                    'legacy_logo' => (string) ($definition['logo'] ?? ''),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $blockData
     * @return list<array<string, mixed>>
     */
    public static function forEditor(array $blockData): array
    {
        return collect(self::documentsForStorage($blockData))
            ->map(fn (array $document): array => [
                ...$document,
                'label' => self::label($document),
                'brand_mark' => self::displayMark($document),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $blockData
     * @return Collection<int, array<string, mixed>>
     */
    public static function visibleForBlockData(array $blockData): Collection
    {
        return collect(self::documentsForStorage($blockData))
            ->map(fn (array $document): array => [
                'id' => $document['id'],
                'key' => $document['id'],
                'type' => $document['type'],
                'label' => self::label($document),
                'number' => $document['value'],
                'value' => $document['value'],
                'brand_mark' => self::displayMark($document),
            ])
            ->values();
    }

    /**
     * @param  array<string, mixed>  $document
     */
    public static function label(array $document): string
    {
        if (($document['type'] ?? '') === 'other' && filled($document['custom_label'] ?? null)) {
            return (string) $document['custom_label'];
        }

        return (string) (self::definitions()[$document['type'] ?? '']['label'] ?? 'وثيقة');
    }

    public static function defaultLogo(string $type): ?string
    {
        $logo = self::definitions()[$type]['logo'] ?? null;

        return filled($logo) ? (string) $logo : null;
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>|null
     */
    protected static function displayMark(array $document): ?array
    {
        $mark = BlockBrandMark::forDisplay(
            is_array($document['brand_mark'] ?? null) ? $document['brand_mark'] : null
        );

        if ($mark !== null) {
            return $mark;
        }

        $legacyLogo = (string) ($document['legacy_logo'] ?? '');

        if ($legacyLogo === '') {
            return null;
        }

        return [
            'type' => 'image',
            'value' => '',
            'color' => '',
            'url' => asset($legacyLogo),
        ];
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    protected static function normalizeStoredDocument(array $document, int $index): array
    {
        $type = (string) ($document['type'] ?? 'other');

        if (! array_key_exists($type, self::definitions())) {
            $type = 'other';
        }

        return [
            'id' => filled($document['id'] ?? null) ? (string) $document['id'] : 'document-'.$index,
            'type' => $type,
            'custom_label' => trim((string) ($document['custom_label'] ?? '')),
            'value' => trim((string) ($document['value'] ?? '')),
            'brand_mark' => BlockBrandMark::normalizeStored(
                is_array($document['brand_mark'] ?? null) ? $document['brand_mark'] : null
            ),
            'legacy_logo' => trim((string) ($document['legacy_logo'] ?? '')),
        ];
    }
}
