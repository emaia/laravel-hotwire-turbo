<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Response as TurboResponse;

class TurboStreamBuilder implements StreamInterface
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

    public function replace(string $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::replace($target, $content));

        return $this;
    }

    public function update(string $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::update($target, $content));

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

    public function morph(string $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::morph($target, $content));

        return $this;
    }

    public function refresh(): static
    {
        $this->streams->add(Stream::refresh());

        return $this;
    }

    public function render(): string
    {
        return $this->streams->render();
    }

    public function respond(int $status = 200, array $headers = []): TurboResponse
    {
        return new TurboResponse($this->streams, $status, $headers);
    }
}
