<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Illuminate\Support\Facades\Blade;

it('returns response with turbo stream content type', function () {
    $response = turbo_stream_view(view('turbo::turbo-stream', [
        'action' => Action::APPEND,
        'target' => 'messages',
        'targets' => '',
        'content' => '<p>Hi</p>',
    ]));

    expect($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html');
    expect($response->getContent())->toContain('<p>Hi</p>');
});

it('renders a view by name', function () {
    // Register an inline view for testing
    app('view')->addNamespace('test', __DIR__.'/views');

    // Use Blade::render as a view object
    $html = Blade::render('<x-turbo::stream action="remove" target="modal" />');
    $response = turbo_stream_view(view()->make('turbo::turbo-stream', [
        'action' => Action::REMOVE,
        'target' => 'modal',
        'targets' => '',
        'content' => '',
    ]));

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html');
    expect($response->getContent())->toContain('action="remove"');
});

it('accepts a View object directly', function () {
    $view = view('turbo::turbo-stream', [
        'action' => Action::APPEND,
        'target' => 'list',
        'targets' => '',
        'content' => '<li>Item</li>',
    ]);

    $response = turbo_stream_view($view);

    expect($response->getContent())
        ->toContain('action="append"')
        ->toContain('<li>Item</li>');
});

it('works via response macro', function () {
    $response = response()->turboStreamView(view('turbo::turbo-stream', [
        'action' => Action::UPDATE,
        'target' => 'counter',
        'targets' => '',
        'content' => '<span>42</span>',
    ]));

    expect($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html');
    expect($response->getContent())->toContain('action="update"');
});
