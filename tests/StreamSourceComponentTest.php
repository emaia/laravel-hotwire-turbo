<?php

use Illuminate\Support\Facades\Blade;

it('renders a basic turbo stream source', function () {
    $html = Blade::render('<x-turbo::stream-source src="/messages/stream" />');

    expect($html)
        ->toContain('<turbo-stream-source')
        ->toContain('src="/messages/stream"')
        ->toContain('</turbo-stream-source>');
});

it('passes through arbitrary extra attributes', function () {
    $html = Blade::render('<x-turbo::stream-source src="/feed" data-controller="foo" class="hidden" />');

    expect($html)
        ->toContain('src="/feed"')
        ->toContain('data-controller="foo"')
        ->toContain('class="hidden"');
});

it('accepts websocket urls', function () {
    $html = Blade::render('<x-turbo::stream-source src="wss://example.com/cable" />');

    expect($html)->toContain('src="wss://example.com/cable"');
});
