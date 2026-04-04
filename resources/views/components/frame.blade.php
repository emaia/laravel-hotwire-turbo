@props(['id', 'src' => null, 'loading' => null, 'target' => null, 'disabled' => false])

<turbo-frame id="{{ $id }}" @if($src) src="{{ $src }}" @endif @if($loading) loading="{{ $loading }}" @endif @if($target) target="{{ $target }}" @endif @if($disabled) disabled @endif {{ $attributes }}>{{ $slot }}</turbo-frame>
