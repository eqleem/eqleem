<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TablerIconsCatalog
{
    public const PREFIX = 'tabler';

    /**
     * @return list<string>
     */
    public function all(): array
    {
        return Cache::rememberForever('tabler-icons.catalog.v1', function (): array {
            $path = resource_path('data/tabler-icons.json');

            if (! is_file($path)) {
                return [];
            }

            /** @var mixed $decoded */
            $decoded = json_decode((string) file_get_contents($path), true);

            if (! is_array($decoded)) {
                return [];
            }

            return array_values(array_filter(
                $decoded,
                fn (mixed $name): bool => is_string($name) && $name !== '',
            ));
        });
    }

    /**
     * @return array{
     *     data: list<array{id: string, name: string}>,
     *     meta: array{page: int, per_page: int, total: int, has_more: bool}
     * }
     */
    public function search(?string $query = null, int $page = 1, int $perPage = 96): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $needle = Str::lower(trim((string) $query));

        $names = $this->all();

        if ($needle !== '') {
            $names = array_values(array_filter(
                $names,
                fn (string $name): bool => str_contains(Str::lower($name), $needle),
            ));
        }

        $total = count($names);
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($names, $offset, $perPage);

        return [
            'data' => array_map(
                fn (string $name): array => [
                    'id' => self::PREFIX.':'.$name,
                    'name' => $name,
                ],
                $slice,
            ),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => ($offset + $perPage) < $total,
            ],
        ];
    }

    public function exists(string $iconId): bool
    {
        $name = $this->nameFromId($iconId);

        if ($name === null) {
            return false;
        }

        return in_array($name, $this->all(), true);
    }

    public function nameFromId(string $iconId): ?string
    {
        $iconId = trim($iconId);

        if (str_starts_with($iconId, self::PREFIX.':')) {
            $name = substr($iconId, strlen(self::PREFIX) + 1);
        } else {
            $name = $iconId;
        }

        $name = trim($name);

        return $name !== '' ? $name : null;
    }

    public function normalizeId(string $iconId): ?string
    {
        $name = $this->nameFromId($iconId);

        if ($name === null || ! $this->exists($name)) {
            return null;
        }

        return self::PREFIX.':'.$name;
    }
}
