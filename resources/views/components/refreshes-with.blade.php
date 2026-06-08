@props([
    'method' => null,
    'scroll' => null,
])

@if ($method)<meta name="turbo-refresh-method" content="{{ $method }}">
@endif
@if ($scroll)<meta name="turbo-refresh-scroll" content="{{ $scroll }}">
@endif
