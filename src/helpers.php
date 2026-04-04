<?php

use Emaia\LaravelHotwireTurbo\TurboStreamBuilder;

if (! function_exists('turbo_stream')) {
    function turbo_stream(): TurboStreamBuilder
    {
        return new TurboStreamBuilder;
    }
}
