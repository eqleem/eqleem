<?php

use App\Support\CtaLink;
use Tests\TestCase;

uses(TestCase::class);

it('marks pages and forms as item-only link types', function () {
    expect(CtaLink::contentTypeRequiresItem('pages'))->toBeTrue()
        ->and(CtaLink::contentTypeRequiresItem('forms'))->toBeTrue()
        ->and(CtaLink::contentTypeRequiresItem('blog'))->toBeFalse()
        ->and(CtaLink::contentTypeHasSectionRoute('pages'))->toBeFalse()
        ->and(CtaLink::contentTypeHasSectionRoute('forms'))->toBeFalse()
        ->and(CtaLink::contentTypeHasItemRoute('pages'))->toBeTrue()
        ->and(CtaLink::contentTypeHasItemRoute('forms'))->toBeTrue();
});

it('exposes item-only picker flags for pages and forms', function () {
    $options = collect(CtaLink::blockLinkPickerOptions())->keyBy('key');

    expect($options->get('pages'))->toMatchArray([
        'supports_section' => false,
        'supports_item' => true,
    ])->and($options->get('forms'))->toMatchArray([
        'supports_section' => false,
        'supports_item' => true,
    ])->and($options->get('blog'))->toMatchArray([
        'supports_section' => true,
        'supports_item' => true,
    ]);
});

it('does not allow section keys for pages and forms', function () {
    $keys = CtaLink::allowedBlockLinkTypeKeys();

    expect($keys)->toContain('item:pages')
        ->and($keys)->toContain('item:forms')
        ->and($keys)->not->toContain('section:pages')
        ->and($keys)->not->toContain('section:forms')
        ->and($keys)->toContain('section:blog');
});
