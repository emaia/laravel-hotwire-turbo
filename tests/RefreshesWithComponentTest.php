<?php

use Illuminate\Support\Facades\Blade;

it('emits both meta tags when method and scroll are provided', function () {
    $html = Blade::render('<x-turbo::refreshes-with method="morph" scroll="preserve" />');

    expect($html)
        ->toContain('<meta name="turbo-refresh-method" content="morph">')
        ->toContain('<meta name="turbo-refresh-scroll" content="preserve">');
});

it('emits only the method meta tag when scroll is omitted', function () {
    $html = Blade::render('<x-turbo::refreshes-with method="morph" />');

    expect($html)
        ->toContain('<meta name="turbo-refresh-method" content="morph">')
        ->not->toContain('turbo-refresh-scroll');
});

it('emits only the scroll meta tag when method is omitted', function () {
    $html = Blade::render('<x-turbo::refreshes-with scroll="preserve" />');

    expect($html)
        ->toContain('<meta name="turbo-refresh-scroll" content="preserve">')
        ->not->toContain('turbo-refresh-method');
});

it('emits nothing when both props are omitted', function () {
    $html = Blade::render('<x-turbo::refreshes-with />');

    expect(trim($html))->toBe('');
});

it('escapes user-supplied values in the content attribute', function () {
    $html = Blade::render(
        '<x-turbo::refreshes-with :method="$method" />',
        ['method' => 'morph" data-injected="x'],
    );

    expect($html)
        ->toContain('&quot;')
        ->not->toContain('data-injected="x');
});
