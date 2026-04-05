<?php

use Emaia\LaravelHotwireTurbo\TurboStreamBuilder;
use Emaia\LaravelHotwireTurbo\Views\RecordIdentifier;

if (! function_exists('turbo_stream')) {
    function turbo_stream(): TurboStreamBuilder
    {
        return new TurboStreamBuilder;
    }
}

if (! function_exists('dom_id')) {
    function dom_id(object $model, string $prefix = ''): string
    {
        return (new RecordIdentifier($model))->domId($prefix ?: null);
    }
}

if (! function_exists('dom_class')) {
    function dom_class(object $model, string $prefix = ''): string
    {
        return (new RecordIdentifier($model))->domClass($prefix ?: null);
    }
}
