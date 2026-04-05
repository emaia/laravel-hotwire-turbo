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

    public function fromTurboFrame(string $frame): static
    {
        return $this->withHeader('Turbo-Frame', $frame);
    }
}
