<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Response;
use Emaia\LaravelHotwireTurbo\Stream;
use Emaia\LaravelHotwireTurbo\StreamCollection;

it('sets correct content type header', function () {
    $stream = new Stream(Action::UPDATE, 'target', 'content');
    $response = new Response($stream);

    expect($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html');
});

it('sets correct status code', function () {
    $stream = new Stream(Action::UPDATE, 'target', 'content');
    $response = new Response($stream, 422);

    expect($response->getStatusCode())->toBe(422);
});

it('renders stream content in response body', function () {
    $stream = new Stream(Action::APPEND, 'messages', '<p>Hello</p>');
    $response = new Response($stream);

    expect($response->getContent())
        ->toContain('action="append"')
        ->toContain('<p>Hello</p>');
});

it('renders stream collection in response body', function () {
    $collection = new StreamCollection([
        new Stream(Action::APPEND, 'list', '<li>Item</li>'),
        new Stream(Action::REMOVE, 'modal'),
    ]);

    $response = new Response($collection);

    expect($response->getContent())
        ->toContain('action="append"')
        ->toContain('action="remove"');
});

it('works with response macro', function () {
    $stream = new Stream(Action::UPDATE, 'target', 'content');
    $response = response()->turboStream($stream); // @phpstan-ignore method.notFound

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html');
});
