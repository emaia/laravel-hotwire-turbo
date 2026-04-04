<?php

use Illuminate\Support\Facades\Blade;

it('renders @turboNocache directive', function () {
    $html = Blade::render('@turboNocache');

    expect($html)->toContain('<meta name="turbo-cache-control" content="no-cache">');
});

it('renders @turboNoPreview directive', function () {
    $html = Blade::render('@turboNoPreview');

    expect($html)->toContain('<meta name="turbo-cache-control" content="no-preview">');
});

it('renders @turboRefreshMethod directive', function () {
    $html = Blade::render("@turboRefreshMethod('morph')");

    expect($html)->toContain('<meta name="turbo-refresh-method" content="morph">');
});

it('renders @turboRefreshScroll directive', function () {
    $html = Blade::render("@turboRefreshScroll('preserve')");

    expect($html)->toContain('<meta name="turbo-refresh-scroll" content="preserve">');
});
