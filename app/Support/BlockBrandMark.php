<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BlockBrandMark
{
    /**
     * Resolve request input into a stored brand_mark array, or null when cleared.
     * Returns the existing mark unchanged when the request does not change it.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>|null  $existing
     * @return array{type: string, value: string, color: string, path?: string}|null
     */
    public static function resolveStored(Tenant $tenant, int $blockId, array $data, ?array $existing): ?array
    {
        $logo = $data['logo'] ?? null;
        $markType = (string) ($data['brand_mark_type'] ?? '');

        if ($logo instanceof UploadedFile) {
            $path = $logo->storePublicly(
                'tenant-media/'.$tenant->uuid.'/block-links/'.$blockId,
                'spaces',
            );

            return [
                'type' => 'image',
                'value' => '',
                'color' => '',
                'path' => $path,
            ];
        }

        if ((bool) ($data['remove_logo'] ?? false) || $markType === 'none') {
            return null;
        }

        if ($markType === 'emoji') {
            $value = trim((string) ($data['brand_mark_value'] ?? ''));

            if ($value === '') {
                return $existing;
            }

            return [
                'type' => 'emoji',
                'value' => mb_substr($value, 0, 16),
                'color' => '',
            ];
        }

        if ($markType === 'icon') {
            $iconId = app(TablerIconsCatalog::class)->normalizeId((string) ($data['brand_mark_value'] ?? ''));

            if ($iconId === null) {
                return $existing;
            }

            return [
                'type' => 'icon',
                'value' => $iconId,
                'color' => self::normalizeColor($data['brand_mark_color'] ?? null),
            ];
        }

        return self::normalizeStored($existing);
    }

    /**
     * @param  array<string, mixed>|null  $stored
     * @return array{type: string, value: string, color: string, url: string|null}|null
     */
    public static function forEditor(?array $stored): ?array
    {
        $normalized = self::normalizeStored($stored);

        if ($normalized === null) {
            return null;
        }

        return [
            'type' => $normalized['type'],
            'value' => $normalized['value'],
            'color' => $normalized['color'],
            'url' => $normalized['type'] === 'image'
                ? self::urlFromPath($normalized['path'] ?? null)
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $stored
     * @return array{type: string, value: string, color: string, url: string|null}|null
     */
    public static function forDisplay(?array $stored): ?array
    {
        return self::forEditor($stored);
    }

    /**
     * @param  array<string, mixed>|null  $stored
     * @return array{type: string, value: string, color: string, path?: string}|null
     */
    public static function normalizeStored(?array $stored): ?array
    {
        if (! is_array($stored)) {
            return null;
        }

        $type = (string) ($stored['type'] ?? '');

        if ($type === 'emoji' && filled($stored['value'] ?? null)) {
            return [
                'type' => 'emoji',
                'value' => (string) $stored['value'],
                'color' => '',
            ];
        }

        if ($type === 'icon' && filled($stored['value'] ?? null)) {
            return [
                'type' => 'icon',
                'value' => (string) $stored['value'],
                'color' => self::normalizeColor($stored['color'] ?? null),
            ];
        }

        if ($type === 'image' && filled($stored['path'] ?? null)) {
            return [
                'type' => 'image',
                'value' => '',
                'color' => '',
                'path' => (string) $stored['path'],
            ];
        }

        return null;
    }

    public static function normalizeColor(mixed $color): string
    {
        $value = is_string($color) ? trim($color) : '';

        if ($value === '' || in_array(strtolower($value), ['inherit', 'currentcolor', 'none'], true)) {
            return '';
        }

        if (preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) === 1) {
            if (strlen($value) === 4) {
                return sprintf(
                    '#%s%s%s%s%s%s',
                    $value[1],
                    $value[1],
                    $value[2],
                    $value[2],
                    $value[3],
                    $value[3],
                );
            }

            return strtolower($value);
        }

        return '';
    }

    protected static function urlFromPath(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::url($path);
    }
}
