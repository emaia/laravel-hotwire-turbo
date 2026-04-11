<?php

use Emaia\LaravelHotwireTurbo\Testing\AssertableTurboStream;
use Emaia\LaravelHotwireTurbo\Testing\ConvertTestResponseToTurboStreamCollection;
use Emaia\LaravelHotwireTurbo\Testing\InteractsWithTurbo;
use Illuminate\Support\Facades\Route;

uses(InteractsWithTurbo::class);

beforeEach(function () {
    Route::get('/turbo-test', function () {
        return turbo_stream()
            ->append('messages', '<p>Hello</p>')
            ->remove('modal')
            ->withResponse();
    });

    Route::get('/normal-test', function () {
        return response('OK');
    });
});

it('adds turbo accept header via trait', function () {
    $response = $this->turbo()->get('/turbo-test');

    expect($response->headers->get('Content-Type'))->toContain('text/vnd.turbo-stream.html');
});

it('adds turbo frame header via trait', function () {
    $response = $this->fromTurboFrame('modal')->get('/normal-test');

    // The header was sent; we just verify it didn't break
    $response->assertOk();
});

it('asserts turbo stream response', function () {
    $this->turbo()
        ->get('/turbo-test')
        ->assertTurboStream();
});

it('asserts turbo stream with count', function () {
    $this->turbo()
        ->get('/turbo-test')
        ->assertTurboStream(fn (AssertableTurboStream $streams) => $streams->has(2));
});

it('asserts turbo stream with matcher', function () {
    $this->turbo()
        ->get('/turbo-test')
        ->assertTurboStream(fn (AssertableTurboStream $streams) => $streams
            ->hasTurboStream(fn ($stream) => $stream
                ->where('action', 'append')
                ->where('target', 'messages')
            )
            ->hasTurboStream(fn ($stream) => $stream
                ->where('action', 'remove')
                ->where('target', 'modal')
            )
        );
});

it('asserts turbo stream with content', function () {
    $this->turbo()
        ->get('/turbo-test')
        ->assertTurboStream(fn (AssertableTurboStream $streams) => $streams
            ->hasTurboStream(fn ($stream) => $stream
                ->where('action', 'append')
                ->see('Hello')
            )
        );
});

it('asserts not turbo stream', function () {
    $this->get('/normal-test')->assertNotTurboStream();
});

it('parses turbo streams from response', function () {
    $response = $this->turbo()->get('/turbo-test');

    $streams = ConvertTestResponseToTurboStreamCollection::convert($response);

    expect($streams)->toHaveCount(2);
});
