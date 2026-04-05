@php
$targetAttr = '';
if (!empty($target)) {
    $targetAttr = ' target="' . e($target) . '"';
}
if (!empty($targets ?? '')) {
    $targetAttr = ' targets="' . e($targets) . '"';
}

$actionValue = $action instanceof \Emaia\LaravelHotwireTurbo\Enums\Action ? $action->value : $action;

$extraAttrs = '';
foreach (($attributes ?? []) as $attrName => $attrValue) {
    $extraAttrs .= ' ' . e($attrName) . '="' . e($attrValue) . '"';
}
@endphp
<turbo-stream action="{{ $actionValue }}"{!! $targetAttr !!}{!! $extraAttrs !!}>
@if($actionValue !== 'remove')
    <template>
        {!! isset($slot) && $slot->isNotEmpty() ? $slot : $content !!}
    </template>
@endif
</turbo-stream>
