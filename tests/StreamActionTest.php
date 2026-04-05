<?php

use Emaia\LaravelHotwireTurbo\Stream;

it('creates a stream with custom action', function () {
    $html = Stream::action('custom-action', 'target', '<p>Content</p>')->render();

    expect($html)
        ->toContain('action="custom-action"')
        ->toContain('target="target"')
        ->toContain('<p>Content</p>');
});

it('creates a stream with custom attributes', function () {
    $html = Stream::action('log', 'console', '', [
        'data-level' => 'info',
        'data-message' => 'Hello',
    ])->render();

    expect($html)
        ->toContain('action="log"')
        ->toContain('data-level="info"')
        ->toContain('data-message="Hello"');
});

it('works via turbo stream builder', function () {
    $html = turbo_stream()
        ->action('custom-action', 'target', '<p>Content</p>')
        ->append('messages', '<p>Hello</p>')
        ->render();

    expect($html)
        ->toContain('action="custom-action"')
        ->toContain('action="append"');
});

it('escapes attribute values', function () {
    $html = Stream::action('test', 'target', '', [
        'data-value' => '<script>alert("xss")</script>',
    ])->render();

    expect($html)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});
