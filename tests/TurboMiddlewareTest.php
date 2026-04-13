<?php

use Emaia\LaravelHotwireTurbo\Http\Middleware\TurboMiddleware;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::post('/posts', fn () => redirect('/posts'))->middleware(TurboMiddleware::class);
    Route::get('/posts', fn () => response('posts index'));
    Route::get('/no-redirect', fn () => response('ok'))->middleware(TurboMiddleware::class);
});

it('converts redirect to 303 for turbo stream visits', function () {
    $response = $this->post('/posts', [], [
        'Accept' => 'text/vnd.turbo-stream.html, text/html',
    ]);

    $response->assertStatus(303);
    $response->assertRedirect('/posts');
});

it('converts redirect to 303 for turbo frame visits', function () {
    $response = $this->post('/posts', [], [
        'Turbo-Frame' => 'modal',
    ]);

    $response->assertStatus(303);
    $response->assertRedirect('/posts');
});

it('keeps 302 for non-turbo visits', function () {
    $response = $this->post('/posts', [], [
        'Accept' => 'text/html',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect('/posts');
});

it('does not affect non-redirect responses', function () {
    $response = $this->get('/no-redirect', [
        'Accept' => 'text/vnd.turbo-stream.html, text/html',
    ]);

    $response->assertStatus(200);
    $response->assertSee('ok');
});

it('is auto-registered as global middleware by default', function () {
    Route::post('/auto-test', fn () => redirect('/posts'));

    $response = $this->post('/auto-test', [], [
        'Accept' => 'text/vnd.turbo-stream.html, text/html',
    ]);

    $response->assertStatus(303);
});

it('does not convert redirect when middleware is not applied', function () {
    Route::post('/no-turbo-middleware', fn () => redirect('/posts'));

    $response = $this->withoutMiddleware(TurboMiddleware::class)
        ->post('/no-turbo-middleware', [], [
            'Accept' => 'text/vnd.turbo-stream.html, text/html',
        ]);

    $response->assertStatus(302);
});
