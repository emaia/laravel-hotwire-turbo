<?php

namespace Emaia\LaravelHotwireTurbo;

use Illuminate\Support\Collection;

class StreamCollection extends Collection implements StreamInterface
{
    public function __construct($items = [])
    {
        $this->each(function ($item) {
            if (! $item instanceof Stream) {
                throw new \InvalidArgumentException('Collection items must be instances of Stream');
            }
        });

        parent::__construct($items);
    }

    public function render(): string
    {
        return $this->reduce(function ($content, Stream $stream) {
            return $content.$stream->render();
        }, '');
    }

    public static function make($items = [])
    {
        return new static($items);
    }
}
