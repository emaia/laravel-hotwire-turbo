<?php

namespace Emaia\LaravelTurbo;

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
        return view('laravel-turbo::turbo-stream', get_object_vars($this))->render();
    }
}
