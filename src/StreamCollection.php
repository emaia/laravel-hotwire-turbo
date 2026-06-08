<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Exceptions\TurboStreamResponseFailedException;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Stringable;

class StreamCollection extends Collection implements Htmlable, StreamInterface, Stringable
{
    public function __construct($items = [])
    {
        parent::__construct($items);

        $this->each(function ($item) {
            if (! $item instanceof Stream) {
                throw TurboStreamResponseFailedException::nonStreamItem();
            }
        });
    }

    public function render(): string
    {
        return $this->reduce(function ($content, Stream $stream) {
            return $content.$stream->render();
        }, '');
    }

    public function add($item): static
    {
        if (! $item instanceof Stream) {
            throw TurboStreamResponseFailedException::nonStreamItem();
        }

        $this->push($item);

        return $this;
    }

    public static function make($items = [], ...$args): static
    {
        return new static($items);
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
