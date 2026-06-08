<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Exceptions\TurboStreamResponseFailedException;
use Emaia\LaravelHotwireTurbo\Views\RecordIdentifier;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\View;
use Stringable;
use Throwable;

class Stream implements Htmlable, StreamInterface, Stringable
{
    use Macroable;

    protected bool $escapeContent = false;

    protected bool $contentFromView = false;

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
        $this->action = is_string($action)
            ? (Action::tryFrom(strtolower($action)) ?? $action)
            : $action;

        if ($this->action !== Action::REFRESH && empty($target) && empty($targets)) {
            throw TurboStreamResponseFailedException::missingTarget();
        }

        if ($content instanceof View) {
            $this->content = $content->render();
            $this->contentFromView = true;
        }
    }

    /**
     * Set the stream content from a Blade view.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function view(string $view, array $data = []): static
    {
        $this->content = view($view, $data)->render();
        $this->contentFromView = true;

        return $this;
    }

    /**
     * Alias of view() — matches Rails-style "partial" terminology.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function partial(string $view, array $data = []): static
    {
        return $this->view($view, $data);
    }

    /**
     * Toggle HTML escaping of string content at render time.
     * Has no effect when content is a rendered View (already HTML).
     */
    public function escape(bool $escape = true): static
    {
        $this->escapeContent = $escape;

        return $this;
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
     *
     * @throws Throwable
     */
    public static function action(string $action, string|object $target, mixed $content = '', array $attributes = []): static
    {
        return new static($action, self::resolveTarget($target), $content, '', $attributes);
    }

    /**
     * @throws Throwable
     */
    public static function append(string|object $target, mixed $content = ''): static
    {
        return new static(Action::APPEND, self::resolveTarget($target), $content);
    }

    /**
     * @throws Throwable
     */
    public static function prepend(string|object $target, mixed $content = ''): static
    {
        return new static(Action::PREPEND, self::resolveTarget($target), $content);
    }

    /**
     * @throws Throwable
     */
    public static function replace(string|object $target, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::REPLACE, self::resolveTarget($target), $content, attributes: array_filter(['method' => $method]));
    }

    /**
     * @throws Throwable
     */
    public static function update(string|object $target, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::UPDATE, self::resolveTarget($target), $content, attributes: array_filter(['method' => $method]));
    }

    /**
     * @throws Throwable
     */
    public static function remove(string|object $target): static
    {
        return new static(Action::REMOVE, self::resolveTarget($target));
    }

    /**
     * @throws Throwable
     */
    public static function after(string|object $target, mixed $content = ''): static
    {
        return new static(Action::AFTER, self::resolveTarget($target), $content);
    }

    /**
     * @throws Throwable
     */
    public static function before(string|object $target, mixed $content = ''): static
    {
        return new static(Action::BEFORE, self::resolveTarget($target), $content);
    }

    /**
     * @throws Throwable
     */
    public static function appendAll(string $targets, mixed $content = ''): static
    {
        return new static(Action::APPEND, content: $content, targets: $targets);
    }

    /**
     * @throws Throwable
     */
    public static function prependAll(string $targets, mixed $content = ''): static
    {
        return new static(Action::PREPEND, content: $content, targets: $targets);
    }

    /**
     * @throws Throwable
     */
    public static function replaceAll(string $targets, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::REPLACE, content: $content, targets: $targets, attributes: array_filter(['method' => $method]));
    }

    /**
     * @throws Throwable
     */
    public static function updateAll(string $targets, mixed $content = '', ?string $method = null): static
    {
        return new static(Action::UPDATE, content: $content, targets: $targets, attributes: array_filter(['method' => $method]));
    }

    /**
     * @throws Throwable
     */
    public static function removeAll(string $targets): static
    {
        return new static(Action::REMOVE, targets: $targets);
    }

    /**
     * @throws Throwable
     */
    public static function afterAll(string $targets, mixed $content = ''): static
    {
        return new static(Action::AFTER, content: $content, targets: $targets);
    }

    /**
     * @throws Throwable
     */
    public static function beforeAll(string $targets, mixed $content = ''): static
    {
        return new static(Action::BEFORE, content: $content, targets: $targets);
    }

    /**
     * @throws Throwable
     */
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

        $content = $this->escapeContent && is_string($this->content) && ! $this->contentFromView
            ? e($this->content)
            : $this->content;

        return view()->file(__DIR__.'/../resources/views/components/stream.blade.php', [
            'action' => $this->action,
            'target' => $this->target ?: null,
            'targets' => $this->targets ?: null,
            'content' => $content,
            'attributes' => new ComponentAttributeBag(
                array_map(fn ($v) => e((string) $v), $extraAttributes)
            ),
            ...$viewProps,
        ])->render();
    }

    /**
     * @throws Throwable
     */
    public function toHtml(): string
    {
        return $this->render();
    }

    /**
     * @throws Throwable
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
