<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Response as TurboResponse;
use Illuminate\Support\Facades\Blade;

it('returns response with turbo stream content type', function () {
    $response = turbo_stream_view(view('turbo::components.stream', [
        'action' => Action::APPEND,
        'target' => 'messages',
        'content' => '<p>Hi</p>',
    ]));

    expect($response)
        ->toBeInstanceOf(TurboResponse::class)
        ->and($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html')
        ->and($response->getContent())->toContain('<p>Hi</p>');
});

it('renders a view by name', function () {
    $html = Blade::render('<x-turbo::stream action="remove" target="modal" />');
    $response = turbo_stream_view(view()->make('turbo::components.stream', [
        'action' => Action::REMOVE,
        'target' => 'modal',
    ]));

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html');
    expect($response->getContent())->toContain('action="remove"');
});

it('accepts a View object directly', function () {
    $view = view('turbo::components.stream', [
        'action' => Action::APPEND,
        'target' => 'list',
        'content' => '<li>Item</li>',
    ]);

    $response = turbo_stream_view($view);

    expect($response->getContent())
        ->toContain('action="append"')
        ->toContain('<li>Item</li>');
});

it('works via response macro', function () {
    $response = response()->turboStreamView(view('turbo::components.stream', [
        'action' => Action::UPDATE,
        'target' => 'counter',
        'content' => '<span>42</span>',
    ]));

    expect($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html');
    expect($response->getContent())->toContain('action="update"');
});
