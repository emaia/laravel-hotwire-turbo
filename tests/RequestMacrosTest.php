<?php

it('detects turbo stream accept header', function () {
    $response = $this->call('GET', '/', [], [], [], [
        'HTTP_ACCEPT' => 'text/vnd.turbo-stream.html, text/html',
    ]);

    expect(request()->wantsTurboStream())->toBeTrue(); // @phpstan-ignore method.notFound
});

it('returns false when no turbo stream accept header', function () {
    $this->call('GET', '/', [], [], [], [
        'HTTP_ACCEPT' => 'text/html',
    ]);

    expect(request()->wantsTurboStream())->toBeFalse(); // @phpstan-ignore method.notFound
});

it('detects turbo frame header', function () {
    $this->call('GET', '/', [], [], [], [
        'HTTP_TURBO_FRAME' => 'modal',
    ]);

    expect(request()->wasFromTurboFrame())->toBeTrue(); // @phpstan-ignore method.notFound
});

it('detects specific turbo frame', function () {
    $this->call('GET', '/', [], [], [], [
        'HTTP_TURBO_FRAME' => 'modal',
    ]);

    expect(request()->wasFromTurboFrame('modal'))->toBeTrue(); // @phpstan-ignore method.notFound
    expect(request()->wasFromTurboFrame('other'))->toBeFalse(); // @phpstan-ignore method.notFound
});

it('returns false when no turbo frame header', function () {
    $this->call('GET', '/');

    expect(request()->wasFromTurboFrame())->toBeFalse(); // @phpstan-ignore method.notFound
});
