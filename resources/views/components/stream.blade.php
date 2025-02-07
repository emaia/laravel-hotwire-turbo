@props(['action', 'target' => null, 'targets' => null, 'mergeAttrs' => []])

<turbo-stream action="{{ $action }}" target="{{ $target }}">
    @if (($slot?->isNotEmpty() ?? false) && $action !== "remove")
    <template>
        {!! $slot !!}
    </template>
    @endif
</turbo-stream>
