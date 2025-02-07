<?php

namespace Emaia\LaravelHotwireTurbo;

use Illuminate\Http\Response as IlluminateResponse;

class Response extends IlluminateResponse
{
    public function __construct(StreamInterface $content, $status = 200, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->setContent($content);
    }

    public function setContent($content): static
    {
        $this->original = $content;

        $this->header('Content-Type', 'text/vnd.turbo-stream.html');

        if ($content instanceof StreamInterface) {
            $content = $content->render();
        }

        return parent::setContent($content);
    }
}
