<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Response as TurboResponse;

class Turbo
{
    public static function response($content, $status = 200, array $headers = []): Response
    {
        return new TurboResponse($content, $status, $headers);
    }
}
