<?php

namespace Emaia\LaravelTurbo;

use Illuminate\Http\ResponseTrait;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response extends SymfonyResponse
{
    use ResponseTrait;

    public function __construct(StreamInterface $content, $status = 200, array $headers = [])
    {
        parent::__construct();

        $this->headers = new ResponseHeaderBag($headers);

        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
    }

    public function setContent(mixed $content): static
    {
        $this->original = $content;

        $this->header('Content-Type', 'text/vnd.turbo-stream.html');

        if ($content instanceof StreamInterface) {
            $content = $content->render();
        }

        parent::setContent($content);

        return $this;
    }
}
