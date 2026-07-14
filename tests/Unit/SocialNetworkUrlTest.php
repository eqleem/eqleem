<?php

use App\Support\SocialNetworkUrl;
use Tests\TestCase;

uses(TestCase::class);

test('keeps absolute urls unchanged', function () {
    expect(SocialNetworkUrl::resolve('twitter', 'https://x.com/eqleem'))
        ->toBe('https://x.com/eqleem');
});

test('expands network handles into profile urls', function (string $network, string $value, string $expected) {
    expect(SocialNetworkUrl::resolve($network, $value))->toBe($expected);
})->with([
    'twitter handle' => ['twitter', 'eqleem', 'https://x.com/eqleem'],
    'twitter at-handle' => ['twitter', '@eqleem', 'https://x.com/eqleem'],
    'youtube handle' => ['youtube', 'eqleem', 'https://youtube.com/@eqleem'],
    'youtube at-handle' => ['youtube', '@eqleem', 'https://youtube.com/@eqleem'],
    'website domain' => ['website', 'eqleem.com', 'https://eqleem.com'],
]);
