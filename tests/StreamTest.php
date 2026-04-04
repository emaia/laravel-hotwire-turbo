<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Stream;
use Illuminate\Support\Facades\Blade;

it('renders a turbo stream with target', function () {
    $stream = new Stream(Action::UPDATE, 'my-target', '<p>Hello</p>');
    $html = $stream->render();

    expect($html)
        ->toContain('action="update"')
        ->toContain('target="my-target"')
        ->toContain('<p>Hello</p>');
});

it('renders a turbo stream with targets css selector', function () {
    $stream = new Stream(Action::UPDATE, targets: '.my-class', content: '<p>Hello</p>');
    $html = $stream->render();

    expect($html)
        ->toContain('action="update"')
        ->toContain('targets=".my-class"')
        ->toContain('<p>Hello</p>');
});

it('throws exception when neither target nor targets provided', function () {
    new Stream(Action::UPDATE, '', '');
})->throws(InvalidArgumentException::class, 'Either target or targets must be provided');

it('renders a view as content', function () {
    $view = Blade::render('<div>{{ $name }}</div>', ['name' => 'Test']);
    $stream = new Stream(Action::REPLACE, 'box', $view);

    expect($stream->render())->toContain('<div>Test</div>');
});

it('renders remove action without template', function () {
    $stream = Stream::remove('my-target');
    $html = $stream->render();

    expect($html)
        ->toContain('action="remove"')
        ->toContain('target="my-target"')
        ->not->toContain('<template>');
});

it('supports all turbo 8 actions', function (Action $action) {
    $stream = new Stream($action, 'target', 'content');

    expect($stream->render())->toContain("action=\"{$action->value}\"");
})->with([
    Action::APPEND,
    Action::PREPEND,
    Action::REPLACE,
    Action::UPDATE,
    Action::REMOVE,
    Action::AFTER,
    Action::BEFORE,
    Action::MORPH,
    Action::REFRESH,
]);

describe('fluent factory methods', function () {
    it('creates append stream', function () {
        $html = Stream::append('target', 'content')->render();
        expect($html)->toContain('action="append"')->toContain('target="target"');
    });

    it('creates prepend stream', function () {
        $html = Stream::prepend('target', 'content')->render();
        expect($html)->toContain('action="prepend"');
    });

    it('creates replace stream', function () {
        $html = Stream::replace('target', 'content')->render();
        expect($html)->toContain('action="replace"');
    });

    it('creates update stream', function () {
        $html = Stream::update('target', 'content')->render();
        expect($html)->toContain('action="update"');
    });

    it('creates remove stream', function () {
        $html = Stream::remove('target')->render();
        expect($html)->toContain('action="remove"');
    });

    it('creates after stream', function () {
        $html = Stream::after('target', 'content')->render();
        expect($html)->toContain('action="after"');
    });

    it('creates before stream', function () {
        $html = Stream::before('target', 'content')->render();
        expect($html)->toContain('action="before"');
    });

    it('creates morph stream', function () {
        $html = Stream::morph('target', 'content')->render();
        expect($html)->toContain('action="morph"');
    });

    it('creates refresh stream', function () {
        $html = Stream::refresh()->render();
        expect($html)->toContain('action="refresh"');
    });
});
