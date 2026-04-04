@php
$targetAttr = '';
if (!empty($target)) {
    $targetAttr = ' target="' . e($target) . '"';
}
if (!empty($targets ?? '')) {
    $targetAttr = ' targets="' . e($targets) . '"';
}
@endphp
<turbo-stream action="{{ $action }}"{!! $targetAttr !!}>
@if($action !== 'remove')
    <template>
        {!! isset($slot) && $slot->isNotEmpty() ? $slot : $content !!}
    </template>
@endif
</turbo-stream>
