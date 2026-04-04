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

    /**
     * @throws Throwable
     */
    public function render(): string
    {
        return view('turbo::turbo-stream', get_object_vars($this))->render(); // @phpstan-ignore argument.type
    }
}
