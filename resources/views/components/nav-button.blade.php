@props(['href', 'variant' => 'primary'])

@php
    $classes = match($variant) {
        'primary' => 'px-5 py-2 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-medium transition-colors',
        'secondary' => 'text-gray-600 hover:text-gray-900 transition-colors',
        default => 'px-5 py-2 bg-coral-500 text-white rounded-full hover:bg-coral-600 font-medium transition-colors',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
