<?php

use Illuminate\Support\Facades\Blade;

it('renders a basic turbo frame', function () {
    $html = Blade::render('<x-turbo::frame id="my-frame">Content</x-turbo::frame>');

    expect($html)
        ->toContain('<turbo-frame id="my-frame"')
        ->toContain('Content')
        ->toContain('</turbo-frame>');
});

it('renders with src attribute', function () {
    $html = Blade::render('<x-turbo::frame id="lazy" src="/load">Loading...</x-turbo::frame>');

    expect($html)
        ->toContain('id="lazy"')
        ->toContain('src="/load"');
});

it('renders with loading attribute', function () {
    $html = Blade::render('<x-turbo::frame id="lazy" src="/load" loading="lazy">Loading...</x-turbo::frame>');

    expect($html)->toContain('loading="lazy"');
});

it('renders with target attribute', function () {
    $html = Blade::render('<x-turbo::frame id="nav" target="_top">Link</x-turbo::frame>');

    expect($html)->toContain('target="_top"');
});

it('renders with disabled attribute', function () {
    $html = Blade::render('<x-turbo::frame id="locked" :disabled="true">Locked</x-turbo::frame>');

    expect($html)->toContain('disabled');
});

it('renders slot content', function () {
    $html = Blade::render('<x-turbo::frame id="test"><p>Inner HTML</p></x-turbo::frame>');

    expect($html)->toContain('<p>Inner HTML</p>');
});
