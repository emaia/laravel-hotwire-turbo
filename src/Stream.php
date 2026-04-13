<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class Stream implements StreamInterface
{
    /**
     * @param  array<string, string>  $attributes
     *
     * @throws Throwable
     */
    public function __construct(
        protected Action|string $action,
        protected string $target = '',
        protected mixed $content = '',
        protected string $targets = '',
        protected array $attributes = [],
    ) {
        $isRefresh = $action === Action::REFRESH
            || (is_string($action) && strtolower($action) === Action::REFRESH->value);

        if (! $isRefresh && empty($target) && empty($targets)) {
            throw new InvalidArgumentException('Either target or targets must be provided');
        }

        if ($content instanceof View) {
            $this->content = $content->render();
        }
    }

    /**
     * @param  array<string, string>  $attributes
     */
    public static function action(string $action, string $target, mixed $content = '', array $attributes = []): static
    {
        return new static($action, $target, $content, '', $attributes);
    }

    public static function append(string $target, mixed $content = ''): static
    {
        return new static(Action::APPEND, $target, $content);
    }

    public static function prepend(string $target, mixed $content = ''): static
    {
        return new static(Action::PREPEND, $target, $content);
    }

    public static function replace(string $target, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::REPLACE, $target, $content, attributes: array_filter(['method' => $method]));
    }

    public static function update(string $target, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::UPDATE, $target, $content, attributes: array_filter(['method' => $method]));
    }

    public static function remove(string $target): static
    {
        return new static(Action::REMOVE, $target);
    }

    public static function after(string $target, mixed $content = ''): static
    {
        return new static(Action::AFTER, $target, $content);
    }

    public static function before(string $target, mixed $content = ''): static
    {
        return new static(Action::BEFORE, $target, $content);
    }

    public static function refresh(?string $method = null, ?string $scroll = null, ?string $requestId = null): static
    {
        return new static(Action::REFRESH, attributes: array_filter([
            'method' => $method,
            'scroll' => $scroll,
            'request-id' => $requestId,
        ]));
    }

    /**
     * @throws Throwable
     */
    public function render(): string
    {
        $viewProps = array_filter([
            'method' => $this->attributes['method'] ?? null,
            'scroll' => $this->attributes['scroll'] ?? null,
            'requestId' => $this->attributes['request-id'] ?? null,
        ]);

        $extraAttributes = array_diff_key(
            $this->attributes,
            array_flip(['method', 'scroll', 'request-id']),
        );

        return view('turbo::components.stream', [
            'action' => $this->action,
            'target' => $this->target ?: null,
            'targets' => $this->targets ?: null,
            'content' => $this->content,
            'attributes' => new ComponentAttributeBag(
                array_map(fn ($v) => e((string) $v), $extraAttributes)
            ),
            ...$viewProps,
        ])->render();
    }
}
