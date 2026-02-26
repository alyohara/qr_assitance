@props(['variant' => 'horizontal'])

@php
    $src = $variant === 'isotype'
        ? asset('branding/logo-pixel-isotype.svg')
        : asset('branding/logo-pixel-horizontal.svg');
@endphp

<img src="{{ $src }}" alt="QR Attendance" {{ $attributes }} />
