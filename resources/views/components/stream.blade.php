@props(['action', 'target' => null, 'targets' => null])

<turbo-stream action="{{ $action }}" @if($target) target="{{ $target }}" @endif @if($targets) targets="{{ $targets }}" @endif>
    @if (($slot?->isNotEmpty() ?? false) && $action !== "remove")
    <template>
        {!! $slot !!}
    </template>
    @endif
</turbo-stream>
