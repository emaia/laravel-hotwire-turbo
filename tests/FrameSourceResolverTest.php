<?php

use Emaia\LaravelHotwireTurbo\Http\FrameSourceResolver;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Log;

function frameSourceRequest(array $input = [], ?string $header = null, bool $fromFrame = true): Request
{
    $server = ['HTTP_HOST' => 'localhost'];

    if ($fromFrame) {
        $server['HTTP_TURBO_FRAME'] = 'modal';
    }

    if ($header !== null) {
        $server['HTTP_X_TURBO_FRAME_SRC'] = $header;
    }

    $request = Request::create('http://localhost/tasks', 'POST', $input, [], [], $server);
    $request->setLaravelSession(app('session.store'));

    return $request;
}

it('returns the first valid explicit source without changing its representation', function () {
    $request = frameSourceRequest(
        ['_turbo_frame_src' => '/tasks/create?tag=alpha&tag=beta#errors'],
        '/tasks/create?from=header',
    );

    expect(app(FrameSourceResolver::class)->resolve($request))
        ->toBe('/tasks/create?tag=alpha&tag=beta#errors');
});

it('accepts absolute HTTP URLs from trusted hosts without rewriting them', function () {
    config()->set('turbo.trusted_redirect_hosts', ['staging.example.com']);
    $source = 'HTTPS://STAGING.EXAMPLE.COM:8443/tasks/create?status=pending#form';

    expect(app(FrameSourceResolver::class)->resolve(
        frameSourceRequest(['_turbo_frame_src' => $source]),
    ))->toBe($source);
});

it('preserves well-formed percent-encoded URL data', function (string $source) {
    expect(app(FrameSourceResolver::class)->resolve(
        frameSourceRequest(['_turbo_frame_src' => $source]),
    ))->toBe($source);
})->with([
    'encoded query data' => '/tasks?pattern=%5Cd%2B&line=%0A',
    'nested encoding' => '/tasks/%5Carchive?next=%255Cserver',
    'absolute URL' => 'https://localhost/tasks?pattern=%5Cd%2B#results',
]);

it('tries the header after an invalid input candidate', function () {
    $request = frameSourceRequest(
        ['_turbo_frame_src' => 'https://evil.example/phishing'],
        '/tasks/create?status=in_progress',
    );

    expect(app(FrameSourceResolver::class)->resolve($request))
        ->toBe('/tasks/create?status=in_progress');
});

it('ignores non-string input candidates without throwing a type error', function () {
    $request = frameSourceRequest(
        ['_turbo_frame_src' => ['/tasks/create']],
        '/tasks/create?status=pending',
    );

    expect(app(FrameSourceResolver::class)->resolve($request))
        ->toBe('/tasks/create?status=pending');
});

it('returns null when the request did not come from a Turbo Frame', function () {
    $request = frameSourceRequest(
        ['_turbo_frame_src' => '/tasks/create'],
        fromFrame: false,
    );

    expect(app(FrameSourceResolver::class)->resolve($request))->toBeNull();
});

it('does not expose the session fallback through explicit source resolution', function () {
    $request = frameSourceRequest();
    $request->session()->setPreviousUrl('http://localhost/dashboard');
    $request->headers->set('Referer', 'http://localhost/previous-page');

    expect(app(FrameSourceResolver::class)->resolve($request))->toBeNull();
});

it('uses a sanitized session URL only for validation redirects', function () {
    $request = frameSourceRequest();
    $request->session()->setPreviousUrl('/dashboard?status=pending');

    expect(app(FrameSourceResolver::class)->resolveValidationRedirect($request))
        ->toBe('/dashboard?status=pending');
});

it('uses the session attached to the received request', function () {
    session()->setPreviousUrl('/container-session');
    $request = frameSourceRequest();
    $requestSession = new Store('request-session', new ArraySessionHandler(120));
    $requestSession->setPreviousUrl('/request-session');
    $request->setLaravelSession($requestSession);

    expect(app(FrameSourceResolver::class)->resolveValidationRedirect($request))
        ->toBe('/request-session');
});

it('does not consult a session when an explicit source is valid', function () {
    $request = Request::create('http://localhost/tasks', 'POST', [
        '_turbo_frame_src' => '/tasks/create',
    ], [], [], [
        'HTTP_HOST' => 'localhost',
        'HTTP_TURBO_FRAME' => 'modal',
    ]);

    expect(app(FrameSourceResolver::class)->resolveValidationRedirect($request))
        ->toBe('/tasks/create');
});

it('uses the compatibility session when the received request has no session attached', function () {
    session()->setPreviousUrl('/compatibility-session');
    $request = Request::create('http://localhost/tasks', 'POST', [], [], [], [
        'HTTP_HOST' => 'localhost',
        'HTTP_TURBO_FRAME' => 'modal',
    ]);

    expect(app(FrameSourceResolver::class)->resolveValidationRedirect($request))
        ->toBe('/compatibility-session');
});

it('tries a valid session fallback after invalid explicit candidates', function () {
    $request = frameSourceRequest(
        ['_turbo_frame_src' => 'javascript:alert(1)'],
        'https://evil.example/phishing',
    );
    $request->session()->setPreviousUrl('/dashboard');

    expect(app(FrameSourceResolver::class)->resolveValidationRedirect($request))
        ->toBe('/dashboard');
});

it('throws when no safe validation redirect source exists', function () {
    $request = frameSourceRequest(
        ['_turbo_frame_src' => 'javascript:alert(1)'],
        'https://evil.example/phishing',
    );
    $request->session()->setPreviousUrl('');

    app(FrameSourceResolver::class)->resolveValidationRedirect($request);
})->throws(RuntimeException::class, 'unable to determine a safe frame source URL');

it('rejects unsafe and ambiguous redirect candidates', function (mixed $source) {
    expect(app(FrameSourceResolver::class)->resolve(
        frameSourceRequest(['_turbo_frame_src' => $source]),
    ))->toBeNull();
})->with([
    'javascript scheme' => 'javascript:alert(1)',
    'data scheme' => 'data:text/html,hello',
    'mailto scheme' => 'mailto:user@example.com',
    'protocol relative' => '//evil.example/path',
    'triple slash' => '///evil.example/path',
    'path relative' => 'tasks/create',
    'query relative' => '?status=pending',
    'fragment relative' => '#form',
    'raw backslash' => '/\\evil.example/path',
    'backslash scheme separator' => 'http:\\evil.example/path',
    'missing host' => 'https:///evil.example/path',
    'leading whitespace' => ' https://evil.example/path',
    'trusted-looking backslash authority' => 'https://evil.example\\@localhost/path',
    'userinfo' => 'https://user:secret@localhost/path',
    'malformed percent escape' => '/tasks?search=%GG',
    'array input' => [['/tasks/create']],
    'integer input' => 42,
    'boolean input' => true,
]);

it('rejects browser-differential authorities even when their raw host is trusted', function (string $source, string $trustedHost) {
    config()->set('turbo.trusted_redirect_hosts', [$trustedHost]);

    expect(app(FrameSourceResolver::class)->resolve(
        frameSourceRequest(['_turbo_frame_src' => $source]),
    ))->toBeNull();
})->with([
    'percent-encoded host' => ['https://%6cocalhost/tasks', '%6cocalhost'],
    'invalid bracketed host' => ['https://[localhost]/tasks', '[localhost]'],
    'octal IPv4' => ['https://0177.0.0.1/tasks', '0177.0.0.1'],
    'short IPv4' => ['https://127.1/tasks', '127.1'],
    'integer IPv4' => ['https://2130706433/tasks', '2130706433'],
    'hexadecimal IPv4' => ['https://0x7f.0.0.1/tasks', '0x7f.0.0.1'],
    'single hexadecimal IPv4' => ['https://0x7f000001/tasks', '0x7f000001'],
    'hexadecimal IPv4 final label' => ['https://127.0.0.0x1/tasks', '127.0.0.0x1'],
    'integer IPv4 with trailing dot' => ['https://2130706433./tasks', '2130706433.'],
    'empty port' => ['https://localhost:/tasks', 'localhost'],
]);

it('accepts canonical IP hosts and preserves their ports', function (string $source, string $trustedHost) {
    config()->set('turbo.trusted_redirect_hosts', [$trustedHost]);

    expect(app(FrameSourceResolver::class)->resolve(
        frameSourceRequest(['_turbo_frame_src' => $source]),
    ))->toBe($source);
})->with([
    'IPv4' => ['https://127.0.0.1:8443/tasks', '127.0.0.1'],
    'IPv6' => ['https://[::1]:8443/tasks', 'https://[::1]'],
]);

it('does not include rejected URLs or trusted hosts in warning logs', function () {
    Log::spy();
    config()->set('turbo.trusted_redirect_hosts', ['private.internal.example']);
    $secret = 'https://evil.example/path?token=super-secret';

    app(FrameSourceResolver::class)->resolve(
        frameSourceRequest(['_turbo_frame_src' => $secret]),
    );

    Log::shouldHaveReceived('warning')->once()->withArgs(function (string $message, array $context) use ($secret): bool {
        $logged = $message.json_encode($context, JSON_UNESCAPED_SLASHES);

        return ! str_contains($logged, $secret)
            && ! str_contains($logged, 'super-secret')
            && ! str_contains($logged, 'private.internal.example')
            && array_keys($context) === ['source', 'reason', 'type', 'length']
            && ($context['source'] ?? null) === 'input';
    });
});
