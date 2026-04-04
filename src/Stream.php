<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class Stream implements StreamInterface
{
    /**
     * @throws Throwable
     */
    public function __construct(
        protected Action $action,
        protected string $target = '',
        protected mixed $content = '',
        protected string $targets = '',
    ) {
        if (empty($target) && empty($targets)) {
            throw new InvalidArgumentException('Either target or targets must be provided');
        }

        if ($content instanceof View) {
            $this->content = $content->render();
        }
    }

    public static function append(string $target, mixed $content = ''): static
    {
        return new static(Action::APPEND, $target, $content);
    }

    public static function prepend(string $target, mixed $content = ''): static
    {
        return new static(Action::PREPEND, $target, $content);
    }

    public static function replace(string $target, mixed $content = ''): static
    {
        return new static(Action::REPLACE, $target, $content);
    }

    public static function update(string $target, mixed $content = ''): static
    {
        return new static(Action::UPDATE, $target, $content);
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

    public static function morph(string $target, mixed $content = ''): static
    {
        return new static(Action::MORPH, $target, $content);
    }

    public static function refresh(): static
    {
        return new static(Action::REFRESH, 'body');
    }

    /**
     * @throws Throwable
     */
    public function render(): string
    {
        return view('turbo::turbo-stream', get_object_vars($this))->render();
    }
}
