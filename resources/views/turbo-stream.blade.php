<turbo-stream action="{{ $action }}" target="{{ $target }}">
    <template>
        {!! isset($slot) && $slot->isNotEmpty() ? $slot : $content !!}
    </template>
</turbo-stream>
