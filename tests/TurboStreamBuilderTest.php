<?php

use Emaia\LaravelHotwireTurbo\Response;
use Emaia\LaravelHotwireTurbo\StreamInterface;
use Emaia\LaravelHotwireTurbo\TurboStreamBuilder;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Route;

it('chains multiple stream actions', function () {
    $builder = turbo_stream()
        ->append('messages', '<p>Hello</p>')
        ->remove('modal')
        ->update('counter', '<span>5</span>');

    $html = $builder->render();

    expect($html)
        ->toContain('action="append"')
        ->toContain('action="remove"')
        ->toContain('action="update"');
});

it('returns a TurboResponse from withResponse()', function () {
    $response = turbo_stream()
        ->append('messages', '<p>New</p>')
        ->withResponse();

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html')
        ->and($response->getStatusCode())->toBe(200);
});

it('accepts custom status code in withResponse()', function () {
    $response = turbo_stream()
        ->replace('form', '<form></form>')
        ->withResponse(422);

    expect($response->getStatusCode())->toBe(422);
});

it('supports all stream actions', function () {
    $html = turbo_stream()
        ->append('a', 'c')
        ->prepend('b', 'c')
        ->replace('c', 'c')
        ->update('d', 'c')
        ->remove('e')
        ->after('f', 'c')
        ->before('g', 'c')
        ->morph('h', 'c')
        ->refresh()
        ->render();

    expect($html)
        ->toContain('action="append"')
        ->toContain('action="prepend"')
        ->toContain('action="replace"')
        ->toContain('action="update"')
        ->toContain('action="remove"')
        ->toContain('action="after"')
        ->toContain('action="before"')
        ->toContain('action="morph"')
        ->toContain('action="refresh"');
});

it('implements StreamInterface', function () {
    $builder = turbo_stream()->append('x', 'y');

    expect($builder)->toBeInstanceOf(StreamInterface::class);
});

it('works with response()->turboStream() macro', function () {
    $builder = turbo_stream()->append('x', 'y');
    $response = response()->turboStream($builder); // @phpstan-ignore method.notFound

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getContent())->toContain('action="append"');
});

it('returns instance from helper function', function () {
    expect(turbo_stream())->toBeInstanceOf(TurboStreamBuilder::class);
});

it('implements Responsable', function () {
    $builder = turbo_stream()->append('x', 'y');

    expect($builder)->toBeInstanceOf(Responsable::class);
});

it('can be returned directly as a response', function () {
    Route::get('/turbo-stream-direct', function () {
        return turbo_stream()->append('messages', '<p>Hello</p>');
    });

    $response = $this->get('/turbo-stream-direct');

    expect($response->headers->get('Content-Type'))->toContain('text/vnd.turbo-stream.html')
        ->and($response->getContent())->toContain('action="append"');
});
