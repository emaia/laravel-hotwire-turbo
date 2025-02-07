<?php

namespace Emaia\LaravelHotwireTurbo;

use Ramsey\Collection\AbstractCollection;

class StreamCollection extends AbstractCollection implements StreamInterface
{
    public function getType(): string
    {
        return Stream::class;
    }

    public function render(): string
    {
        $content = '';

        /** @var Stream $stream */
        foreach ($this->data as $stream) {
            $content .= $stream->render();
        }

        return $content;
    }
}
