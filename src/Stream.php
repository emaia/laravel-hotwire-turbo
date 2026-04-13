<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Views\RecordIdentifier;
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

    private static function resolveTarget(string|object $target): string
    {
        if (is_object($target)) {
            return (new RecordIdentifier($target))->domId();
        }

        return $target;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    public static function action(string $action, string|object $target, mixed $content = '', array $attributes = []): static
    {
        return new static($action, self::resolveTarget($target), $content, '', $attributes);
    }

    public static function append(string|object $target, mixed $content = ''): static
    {
        return new static(Action::APPEND, self::resolveTarget($target), $content);
    }

    public static function prepend(string|object $target, mixed $content = ''): static
    {
        return new static(Action::PREPEND, self::resolveTarget($target), $content);
    }

    public static function replace(string|object $target, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::REPLACE, self::resolveTarget($target), $content, attributes: array_filter(['method' => $method]));
    }

    public static function update(string|object $target, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::UPDATE, self::resolveTarget($target), $content, attributes: array_filter(['method' => $method]));
    }

    public static function remove(string|object $target): static
    {
        return new static(Action::REMOVE, self::resolveTarget($target));
    }

    public static function after(string|object $target, mixed $content = ''): static
    {
        return new static(Action::AFTER, self::resolveTarget($target), $content);
    }

    public static function before(string|object $target, mixed $content = ''): static
    {
        return new static(Action::BEFORE, self::resolveTarget($target), $content);
    }

    public static function appendAll(string $targets, mixed $content = ''): static
    {
        return new static(Action::APPEND, content: $content, targets: $targets);
    }

    public static function prependAll(string $targets, mixed $content = ''): static
    {
        return new static(Action::PREPEND, content: $content, targets: $targets);
    }

    public static function replaceAll(string $targets, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::REPLACE, content: $content, targets: $targets, attributes: array_filter(['method' => $method]));
    }

    public static function updateAll(string $targets, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::UPDATE, content: $content, targets: $targets, attributes: array_filter(['method' => $method]));
    }

    public static function removeAll(string $targets): static
    {
        return new static(Action::REMOVE, targets: $targets);
    }

    public static function afterAll(string $targets, mixed $content = ''): static
    {
        return new static(Action::AFTER, content: $content, targets: $targets);
    }

    public static function beforeAll(string $targets, mixed $content = ''): static
    {
        return new static(Action::BEFORE, content: $content, targets: $targets);
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
