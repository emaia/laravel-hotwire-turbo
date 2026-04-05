<?php

use Emaia\LaravelHotwireTurbo\Turbo;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/turbo-if-test', function () {
        return Turbo::if(
            stream: turbo_stream()->remove('modal')->respond(),
            fallback: redirect('/fallback'),
        );
    });
});

it('returns turbo stream when request wants turbo', function () {
    $response = $this->withHeaders([
        'Accept' => 'text/vnd.turbo-stream.html',
    ])->get('/turbo-if-test');

    expect($response->headers->get('Content-Type'))->toContain('text/vnd.turbo-stream.html');
});

it('returns fallback when request does not want turbo', function () {
    $response = $this->get('/turbo-if-test');

    $response->assertRedirect('/fallback');
});

it('wraps StreamInterface in TurboResponse', function () {
    Route::get('/turbo-if-builder', function () {
        return Turbo::if(
            stream: turbo_stream()->remove('modal'),
            fallback: redirect('/fallback'),
        );
    });

    $response = $this->withHeaders([
        'Accept' => 'text/vnd.turbo-stream.html',
    ])->get('/turbo-if-builder');

    expect($response->headers->get('Content-Type'))->toContain('text/vnd.turbo-stream.html');
    expect($response->getContent())->toContain('turbo-stream');
});
