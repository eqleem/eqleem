<?php

namespace App\Support;

use Illuminate\Support\Collection;

class BusinessDocuments
{
    /**
     * @return array<string, array{label: string, logo: string}>
     */
    public static function definitions(): array
    {
        return config('business-documents.documents', []);
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
     * @return Collection<int, array{key: string, label: string, number: string, logo: string}>
     */
    public static function visibleForBlockData(array $blockData): Collection
    {
        $documentNumbers = self::numbersFromBlockData($blockData);

        return collect(self::definitions())
            ->map(function (array $document, string $key) use ($documentNumbers): ?array {
                $number = trim($documentNumbers[$key] ?? '');

                if ($number === '') {
                    return null;
                }

                return [
                    'key' => $key,
                    'label' => $document['label'],
                    'number' => $number,
                    'logo' => $document['logo'],
                ];
            })
            ->filter()
            ->values();
    }
}
