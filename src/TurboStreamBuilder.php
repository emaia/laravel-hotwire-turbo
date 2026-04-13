<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Response as TurboResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Traits\Conditionable;

class TurboStreamBuilder implements Responsable, StreamInterface
{
    use Conditionable;

    protected StreamCollection $streams;

    public function __construct()
    {
        $this->streams = StreamCollection::make();
    }

    public function append(string|object $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::append($target, $content));

        return $this;
    }

    public function prepend(string|object $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::prepend($target, $content));

        return $this;
    }

    public function replace(string|object $target, mixed $content = '', ?string $method = null): static
    {
        $this->streams->add(Stream::replace($target, $content, $method));

        return $this;
    }

    public function update(string|object $target, mixed $content = '', ?string $method = null): static
    {
        $this->streams->add(Stream::update($target, $content, $method));

        return $this;
    }

    public function remove(string|object $target): static
    {
        $this->streams->add(Stream::remove($target));

        return $this;
    }

    public function after(string|object $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::after($target, $content));

        return $this;
    }

    public function before(string|object $target, mixed $content = ''): static
    {
        $this->streams->add(Stream::before($target, $content));

        return $this;
    }

    public function appendAll(string $targets, mixed $content = ''): static
    {
        $this->streams->add(Stream::appendAll($targets, $content));

        return $this;
    }

    public function prependAll(string $targets, mixed $content = ''): static
    {
        $this->streams->add(Stream::prependAll($targets, $content));

        return $this;
    }

    public function replaceAll(string $targets, mixed $content = '', ?string $method = null): static
    {
        $this->streams->add(Stream::replaceAll($targets, $content, $method));

        return $this;
    }

    public function updateAll(string $targets, mixed $content = '', ?string $method = null): static
    {
        $this->streams->add(Stream::updateAll($targets, $content, $method));

        return $this;
    }

    public function removeAll(string $targets): static
    {
        $this->streams->add(Stream::removeAll($targets));

        return $this;
    }

    public function afterAll(string $targets, mixed $content = ''): static
    {
        $this->streams->add(Stream::afterAll($targets, $content));

        return $this;
    }

    public function beforeAll(string $targets, mixed $content = ''): static
    {
        $this->streams->add(Stream::beforeAll($targets, $content));

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
    public function action(string $action, string|object $target, mixed $content = '', array $attributes = []): static
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
