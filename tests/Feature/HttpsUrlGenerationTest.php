<?php

use Illuminate\Support\Facades\URL;

it('generates https urls when app url uses https', function () {
    config(['app.url' => 'https://eqleem.com']);

    URL::forceScheme('https');

    expect(url('/login'))->toStartWith('https://')
        ->and(parse_url((string) url('/login'), PHP_URL_SCHEME))->toBe('https');
});
