<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Unsplash
{
    public function configured(): bool
    {
        return filled(config('services.unsplash.access_key'));
    }

    /**
     * @return list<array{id: string, alt: string|null, thumb: string, url: string, author: string, author_url: string|null}>
     */
    public function photos(?string $query = null, int $perPage = 16): array
    {
        $perPage = max(1, min(30, $perPage));

        $response = $this->client()->get(
            filled($query) ? 'https://api.unsplash.com/search/photos' : 'https://api.unsplash.com/photos',
            filled($query)
                ? ['query' => $query, 'per_page' => $perPage, 'orientation' => 'landscape']
                : ['per_page' => $perPage, 'order_by' => 'popular'],
        );

        if (! $response->successful()) {
            throw new RuntimeException('Unable to fetch Unsplash photos.');
        }

        /** @var list<array<string, mixed>>|array{results?: list<array<string, mixed>>} $payload */
        $payload = $response->json() ?? [];
        $results = filled($query)
            ? ($payload['results'] ?? [])
            : $payload;

        if (! is_array($results)) {
            return [];
        }

        return collect($results)
            ->filter(fn (mixed $photo): bool => is_array($photo) && filled(data_get($photo, 'id')))
            ->map(fn (array $photo): array => $this->mapPhoto($photo))
            ->values()
            ->all();
    }

    /**
     * @return array{id: string, url: string, author: string, author_url: string|null}
     */
    public function select(string $id): array
    {
        $response = $this->client()->get("https://api.unsplash.com/photos/{$id}");

        if (! $response->successful()) {
            throw new RuntimeException('Unable to load Unsplash photo.');
        }

        /** @var array<string, mixed> $photo */
        $photo = $response->json() ?? [];
        $mapped = $this->mapPhoto($photo);

        $downloadLocation = data_get($photo, 'links.download_location');

        if (filled($downloadLocation)) {
            // Required by Unsplash API guidelines when a photo is selected as a cover.
            $this->client()->get((string) $downloadLocation);
        }

        return [
            'id' => $mapped['id'],
            'url' => $mapped['url'],
            'author' => $mapped['author'],
            'author_url' => $mapped['author_url'],
        ];
    }

    private function client(): PendingRequest
    {
        $accessKey = config('services.unsplash.access_key');

        if (! filled($accessKey)) {
            throw new RuntimeException('Unsplash is not configured.');
        }

        return Http::acceptJson()
            ->withToken((string) $accessKey, 'Client-ID')
            ->timeout(15);
    }

    /**
     * @param  array<string, mixed>  $photo
     * @return array{id: string, alt: string|null, thumb: string, url: string, author: string, author_url: string|null}
     */
    private function mapPhoto(array $photo): array
    {
        $utmSource = (string) config('services.unsplash.utm_source', 'eqleem');
        $rawUrl = (string) data_get($photo, 'urls.regular', data_get($photo, 'urls.full', ''));
        $thumb = (string) data_get($photo, 'urls.small', data_get($photo, 'urls.thumb', $rawUrl));
        $author = (string) data_get($photo, 'user.name', 'Unsplash');
        $authorUsername = data_get($photo, 'user.username');
        $authorUrl = filled($authorUsername)
            ? 'https://unsplash.com/@'.$authorUsername.'?utm_source='.urlencode($utmSource).'&utm_medium=referral'
            : null;

        $url = $rawUrl;

        if (filled($url) && ! str_contains($url, 'utm_source=')) {
            $url .= (str_contains($url, '?') ? '&' : '?').'utm_source='.urlencode($utmSource).'&utm_medium=referral';
        }

        return [
            'id' => (string) data_get($photo, 'id'),
            'alt' => data_get($photo, 'alt_description') ?: data_get($photo, 'description'),
            'thumb' => $thumb,
            'url' => $url,
            'author' => $author,
            'author_url' => $authorUrl,
        ];
    }
}
