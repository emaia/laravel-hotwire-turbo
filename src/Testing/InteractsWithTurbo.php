<?php

namespace Emaia\LaravelHotwireTurbo\Testing;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;

/**
 * @mixin MakesHttpRequests
 */
trait InteractsWithTurbo
{
    public function turbo(): static
    {
        return $this->withHeader('Accept', 'text/vnd.turbo-stream.html');
    }

    /**
     * Send requests as a plain (non-Turbo) browser visit. Clears any
     * Turbo-Frame header previously set via fromTurboFrame() and sets
     * Accept: text/html. Useful to assert that an endpoint still returns
     * full-page HTML when accessed directly.
     */
    public function withoutTurbo(): static
    {
        unset($this->defaultHeaders['Turbo-Frame']);

        return $this->withHeader('Accept', 'text/html');
    }

    public function fromTurboFrame(string $frame): static
    {
        return $this->withHeader('Turbo-Frame', $frame);
    }
}
