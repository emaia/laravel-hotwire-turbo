<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Stream;
use Emaia\LaravelHotwireTurbo\StreamCollection;

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

    expect($collection)->toHaveCount(2);
    expect($collection->render())
        ->toContain('action="append"')
        ->toContain('action="prepend"');
});

it('throws exception when adding non-stream via add', function () {
    StreamCollection::make()->add('invalid');
})->throws(InvalidArgumentException::class, 'Collection items must be instances of Stream');

it('creates empty collection with make', function () {
    $collection = StreamCollection::make();

    expect($collection)->toHaveCount(0);
    expect($collection->render())->toBe('');
});
