<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Exceptions\TurboStreamResponseFailedException;
use Emaia\LaravelHotwireTurbo\Response as TurboResponse;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Stringable;

class TurboStreamBuilder implements Htmlable, Responsable, StreamInterface, Stringable
{
    use Conditionable, Macroable;

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

    /**
     * Set the content of the most recently added stream from a Blade view.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws TurboStreamResponseFailedException when called before any stream has been added
     */
    public function view(string $view, array $data = []): static
    {
        $this->lastStream(__FUNCTION__)->view($view, $data);

        return $this;
    }

    /**
     * Alias of view() — matches Rails-style "partial" terminology.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws TurboStreamResponseFailedException when called before any stream has been added
     */
    public function partial(string $view, array $data = []): static
    {
        return $this->view($view, $data);
    }

    /**
     * Toggle HTML escaping on the most recently added stream.
     *
     * @throws TurboStreamResponseFailedException when called before any stream has been added
     */
    public function escape(bool $escape = true): static
    {
        $this->lastStream(__FUNCTION__)->escape($escape);

        return $this;
    }

    private function lastStream(string $caller): Stream
    {
        $stream = $this->streams->last();

        if (! $stream instanceof Stream) {
            throw TurboStreamResponseFailedException::builderEmpty($caller);
        }

        return $stream;
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

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
