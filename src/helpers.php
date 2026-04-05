<?php

use Emaia\LaravelHotwireTurbo\TurboStreamBuilder;
use Emaia\LaravelHotwireTurbo\Views\RecordIdentifier;
use Illuminate\Http\Response;
use Illuminate\View\View;

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

if (! function_exists('turbo_stream_view')) {
    function turbo_stream_view(string|View $view, array $data = []): Response
    {
        if (! $view instanceof View) {
            $view = view($view, $data);
        }

        return new Response($view->render(), 200, [
            'Content-Type' => 'text/vnd.turbo-stream.html',
        ]);
    }
}
