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
        protected string $target,
        protected mixed $content = '')
    {
        if (empty($target)) {
            throw new InvalidArgumentException('Target ID cannot be empty');
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
        return view('turbo::turbo-stream', get_object_vars($this))->render();
    }
}
