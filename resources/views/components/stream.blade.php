@props([
    'action',
    'target' => null,
    'targets' => null,
    'method' => null,
    'scroll' => null,
    'requestId' => null,
    'content' => null,
])

@php
$actionValue = $action instanceof \Emaia\LaravelHotwireTurbo\Enums\Action
    ? $action->value
    : $action;

$noTemplate = in_array($actionValue, ['remove', 'refresh']);

$slotContent = isset($slot) && $slot->isNotEmpty() ? (string) $slot : $content;

$mergeAttrs = array_filter([
    'target'     => $target,
    'targets'    => $targets,
    'method'     => $method,
    'scroll'     => $scroll,
    'request-id' => $requestId,
], fn ($v) => $v !== null);
@endphp

<turbo-stream action="{{ $actionValue }}" {{ $attributes->merge($mergeAttrs) }}>
    @if (! $noTemplate)
    <template>
        {!! $slotContent !!}
    </template>
    @endif
</turbo-stream>
