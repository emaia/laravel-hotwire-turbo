<?php

namespace Emaia\LaravelHotwireTurbo;

use Illuminate\Support\Collection;

class StreamCollection extends Collection implements StreamInterface
{
    public function __construct($items = [])
    {
        parent::__construct($items);

        $this->each(function ($item) {
            if (! $item instanceof Stream) {
                throw new \InvalidArgumentException('Collection items must be instances of Stream');
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
            throw new \InvalidArgumentException('Collection items must be instances of Stream');
        }

        $this->push($item);

        return $this;
    }

    public static function make($items = [], ...$args): static
    {
        return new static($items);
    }
}
