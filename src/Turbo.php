<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Response as TurboResponse;
use Symfony\Component\HttpFoundation\Response;

class Turbo
{
    public static function response($content, $status = 200, array $headers = []): TurboResponse
    {
        return new TurboResponse($content, $status, $headers);
    }

    public static function if(Response|StreamInterface $stream, Response $fallback, ?string $frame = null): Response
    {
        if (! request()->wantsTurboStream()) {
            return $fallback;
        }

        if ($frame && ! request()->wasFromTurboFrame($frame)) {
            return $fallback;
        }

        if ($stream instanceof StreamInterface) {
            return new TurboResponse($stream);
        }

        return $stream;
    }
}
