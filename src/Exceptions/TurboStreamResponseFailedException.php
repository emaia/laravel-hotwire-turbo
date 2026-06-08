<?php

namespace Emaia\LaravelHotwireTurbo\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a Turbo Stream response cannot be built — missing target,
 * invalid collection item, or builder used out of order.
 *
 * Extends {@see InvalidArgumentException} so existing catch blocks keep working.
 */
class TurboStreamResponseFailedException extends InvalidArgumentException
{
    public static function missingTarget(): self
    {
        return new self('Either target or targets must be provided');
    }

    public static function builderEmpty(string $method): self
    {
        return new self(sprintf(
            'Cannot call %s() on TurboStreamBuilder before adding a stream.',
            $method,
        ));
    }

    public static function nonStreamItem(): self
    {
        return new self('Collection items must be instances of Stream');
    }
}
