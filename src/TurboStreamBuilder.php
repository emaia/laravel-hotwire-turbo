<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Response as TurboResponse;
use Illuminate\Contracts\Support\Responsable;

class TurboStreamBuilder implements Responsable, StreamInterface
{
    protected StreamCollection $streams;

    public function __construct()
    {
        $this->streams = StreamCollection::make();
    }

    public function append(string $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::append($target, $content));

        return $this;
    }

    public function prepend(string $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::prepend($target, $content));

        return $this;
    }

    public function replace(string $target, mixed $content = '', ?string $method = null): static
    {
        $this->streams->add(Stream::replace($target, $content, $method));

        return $this;
    }

    public function update(string $target, mixed $content = '', ?string $method = null): static
    {
        $this->streams->add(Stream::update($target, $content, $method));

        return $this;
    }

    public function remove(string $target): static
    {
        $this->streams->add(Stream::remove($target));

        return $this;
    }

    public function after(string $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::after($target, $content));

        return $this;
    }

    public function before(string $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::before($target, $content));

        return $this;
    }

    public function refresh(?string $method = null, ?string $scroll = null, ?string $requestId = null): static
    {
        $this->streams->add(Stream::refresh($method, $scroll, $requestId));

        return $this;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    public function action(string $action, string $target, mixed $content = '', array $attributes = []): static
    {
        $this->streams->add(Stream::action($action, $target, $content, $attributes));

        return $this;
    }

    public function render(): string
    {
        return $this->streams->render();
    }

    public function withResponse(int $status = 200, array $headers = []): TurboResponse
    {
        return new TurboResponse($this->streams, $status, $headers);
    }

    public function toResponse($request): TurboResponse
    {
        return $this->withResponse();
    }
}
