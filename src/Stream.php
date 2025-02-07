<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Illuminate\View\View;

class Stream implements StreamInterface
{
    public function __construct(
        protected Action $action,
        protected string $target,
        protected mixed $content = '')
    {
        if ($content instanceof View) {
            $this->content = $content->render();
        }
    }

    public function render(): string
    {
        return view('turbo::turbo-stream', get_object_vars($this))->render();
    }
}
