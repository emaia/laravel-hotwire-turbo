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

it('returns the current turbo frame id', function () {
    $this->call('GET', '/', [], [], [], [
        'HTTP_TURBO_FRAME' => 'modal',
    ]);

    expect(request()->turboFrameId())->toBe('modal'); // @phpstan-ignore method.notFound
});

it('returns null when the turbo frame header is empty or absent', function (?string $frame) {
    $server = $frame === null ? [] : ['HTTP_TURBO_FRAME' => $frame];

    $this->call('GET', '/', [], [], [], $server);

    expect(request()->turboFrameId())->toBeNull(); // @phpstan-ignore method.notFound
})->with(['absent' => null, 'empty' => '', 'whitespace' => '   ']);

it('compares non-empty turbo frame ids strictly', function () {
    $this->call('GET', '/', [], [], [], [
        'HTTP_TURBO_FRAME' => '0',
    ]);

    expect(request()->turboFrameId())->toBe('0') // @phpstan-ignore method.notFound
        ->and(request()->wasFromTurboFrame('0'))->toBeTrue() // @phpstan-ignore method.notFound
        ->and(request()->wasFromTurboFrame(''))->toBeFalse(); // @phpstan-ignore method.notFound
});

it('trims turbo frame ids before comparing them', function () {
    $this->call('GET', '/', [], [], [], [
        'HTTP_TURBO_FRAME' => '  modal  ',
    ]);

    expect(request()->turboFrameId())->toBe('modal') // @phpstan-ignore method.notFound
        ->and(request()->wasFromTurboFrame('modal'))->toBeTrue(); // @phpstan-ignore method.notFound
});

it('resolves explicit turbo frame source input through the request macro', function () {
    $this->call('POST', '/', [
        '_turbo_frame_src' => '/tasks/create?status=in_progress',
    ], [], [], [
        'HTTP_TURBO_FRAME' => 'modal',
        'HTTP_X_TURBO_FRAME_SRC' => '/tasks/create?from=header',
    ]);

    expect(request()->turboFrameSource()) // @phpstan-ignore method.notFound
        ->toBe('/tasks/create?status=in_progress');
});

it('falls through invalid source input to a valid source header', function () {
    $this->call('POST', '/', [
        '_turbo_frame_src' => ['https://evil.example'],
    ], [], [], [
        'HTTP_TURBO_FRAME' => 'modal',
        'HTTP_X_TURBO_FRAME_SRC' => '/tasks/create?from=header',
    ]);

    expect(request()->turboFrameSource()) // @phpstan-ignore method.notFound
        ->toBe('/tasks/create?from=header');
});

it('does not expose a frame source outside Turbo Frame requests', function () {
    $this->call('POST', '/', [
        '_turbo_frame_src' => '/tasks/create',
    ]);

    expect(request()->turboFrameId())->toBeNull() // @phpstan-ignore method.notFound
        ->and(request()->turboFrameSource())->toBeNull(); // @phpstan-ignore method.notFound
});

it('reads the turbo request id from the X-Turbo-Request-Id header', function () {
    $this->call('GET', '/', [], [], [], [
        'HTTP_X_TURBO_REQUEST_ID' => 'abc-123',
    ]);

    expect(request()->turboRequestId())->toBe('abc-123'); // @phpstan-ignore method.notFound
});

it('returns null when X-Turbo-Request-Id header is absent', function () {
    $this->call('GET', '/');

    expect(request()->turboRequestId())->toBeNull(); // @phpstan-ignore method.notFound
});
