<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Stream;
use Emaia\LaravelHotwireTurbo\StreamCollection;
use Emaia\LaravelHotwireTurbo\StreamInterface;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;

it('renders multiple streams', function () {
    $collection = new StreamCollection([
        new Stream(Action::APPEND, 'list', '<li>Item</li>'),
        new Stream(Action::REMOVE, 'modal'),
    ]);

    $html = $collection->render();

    expect($html)
        ->toContain('action="append"')
        ->toContain('action="remove"');
});

it('throws exception for invalid items in constructor', function () {
    new StreamCollection(['not-a-stream']);
})->throws(InvalidArgumentException::class, 'Collection items must be instances of Stream');

it('supports add method', function () {
    $collection = StreamCollection::make()
        ->add(Stream::append('list', '<li>One</li>'))
        ->add(Stream::prepend('list', '<li>Zero</li>'));

    expect($collection)->toHaveCount(2)
        ->and($collection->render())
        ->toContain('action="append"')
        ->toContain('action="prepend"');
});

it('throws exception when adding non-stream via add', function () {
    StreamCollection::make()->add('invalid');
})->throws(InvalidArgumentException::class, 'Collection items must be instances of Stream');

it('creates empty collection with make', function () {
    $collection = StreamCollection::make();

    expect($collection)->toHaveCount(0)
        ->and($collection->render())->toBe('');
});

describe('renderable contracts', function () {
    it('implements Htmlable, StreamInterface and Stringable', function () {
        $collection = StreamCollection::make()
            ->add(Stream::append('list', '<li>One</li>'));

        expect($collection)
            ->toBeInstanceOf(Htmlable::class)
            ->toBeInstanceOf(StreamInterface::class)
            ->toBeInstanceOf(Stringable::class);
    });

    it('returns the rendered html string from toHtml()', function () {
        $collection = StreamCollection::make()
            ->add(Stream::append('list', '<li>One</li>'));

        expect($collection->toHtml())
            ->toBeString()
            ->toContain('action="append"');
    });

    it('renders to string when echoed', function () {
        $collection = StreamCollection::make()
            ->add(Stream::append('list', '<li>One</li>'))
            ->add(Stream::remove('modal'));

        expect((string) $collection)
            ->toContain('action="append"')
            ->toContain('action="remove"');
    });

    it('can be echoed in Blade without escaping the turbo-stream tags', function () {
        $collection = StreamCollection::make()
            ->add(Stream::append('list', '<li>One</li>'));

        $html = Blade::render('{{ $streams }}', ['streams' => $collection]);

        expect($html)
            ->toContain('<turbo-stream')
            ->toContain('action="append"')
            ->not->toContain('&lt;turbo-stream');
    });
});
