@props([
    'id',
    'src' => null,
    'loading' => null,
    'target' => null,
    'disabled' => false,
    'refresh' => null,
    'autoscroll' => false,
    'autoscrollBlock' => null,
    'autoscrollBehavior' => null,
    'advance' => null,
    'recurse' => null,
])

@php
$resolvedId = is_object($id) ? dom_id($id) : $id;

$mergeAttrs = array_filter([
    'src'                      => $src,
    'loading'                  => $loading,
    'target'                   => $target,
    'refresh'                  => $refresh,
    'recurse'                  => $recurse,
    'data-turbo-action'        => $advance,
    'data-autoscroll-block'    => $autoscrollBlock,
    'data-autoscroll-behavior' => $autoscrollBehavior,
    'disabled'                 => $disabled ? 'disabled' : null,
    'autoscroll'               => $autoscroll ? 'autoscroll' : null,
], fn ($v) => $v !== null);
@endphp
<turbo-frame id="{{ $resolvedId }}" {{ $attributes->merge($mergeAttrs) }}>{{ $slot }}</turbo-frame>
