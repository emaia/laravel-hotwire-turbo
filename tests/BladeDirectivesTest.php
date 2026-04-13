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

it('renders @turboCdn directive', function () {
    $html = Blade::render('@turboCdn');

    expect($html)->toContain('<script type="module" src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@latest/dist/turbo.es2017-esm.min.js"></script>');
});

it('renders @turboVisitControl directive', function () {
    $html = Blade::render("@turboVisitControl('reload')");

    expect($html)->toContain('<meta name="turbo-visit-control" content="reload">');
});

it('renders @turboRoot directive', function () {
    $html = Blade::render("@turboRoot('/app')");

    expect($html)->toContain('<meta name="turbo-root" content="/app">');
});

it('renders @viewTransition directive', function () {
    $html = Blade::render("@viewTransition('same-origin')");

    expect($html)->toContain('<meta name="view-transition" content="same-origin">');
});

it('renders @turboPrefetch directive with false', function () {
    $html = Blade::render("@turboPrefetch('false')");

    expect($html)->toContain('<meta name="turbo-prefetch" content="false">');
});
