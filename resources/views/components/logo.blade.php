@props(['size' => 'default', 'showBeta' => true])

@php
    $sizeClasses = match($size) {
        'small' => [
            'container' => 'space-x-1.5',
            'heart' => 'text-lg',
            'text' => 'text-base',
            'badge' => 'text-[8px] px-1.5 py-px -top-1.5 -right-6',
        ],
        'large' => [
            'container' => 'space-x-3',
            'heart' => 'text-3xl',
            'text' => 'text-2xl',
            'badge' => 'text-[10px] px-2 py-0.5 -top-2 -right-8',
        ],
        default => [
            'container' => 'space-x-2',
            'heart' => 'text-2xl',
            'text' => 'text-xl',
            'badge' => 'text-[9px] px-1.5 py-px -top-1.5 -right-6',
        ],
    };
@endphp

<a href="{{ url('/' . app()->getLocale()) }}" class="group/logo relative inline-flex items-center {{ $sizeClasses['container'] }}">
    {{-- Heart emoji --}}
    <span class="{{ $sizeClasses['heart'] }} text-coral-500">&#10084;</span>

    {{-- Brand name with relative positioning for badge anchor --}}
    <span class="relative {{ $sizeClasses['text'] }} font-bold">
        <span class="text-gray-900 group-hover/logo:text-coral-500 transition-colors duration-300">Give</span><span class="text-coral-500 group-hover/logo:text-gray-900 transition-colors duration-300">Twice</span>

        @if($showBeta)
            {{-- Beta badge - anchored to top-right of text, playful tilt --}}
            <span
                class="
                    absolute {{ $sizeClasses['badge'] }}
                    bg-violet-600
                    text-white font-semibold uppercase tracking-wide
                    rounded-full
                    shadow-sm
                    rotate-12
                    transition-transform duration-200
                    group-hover/logo:scale-110 group-hover/logo:rotate-6
                    select-none pointer-events-none
                "
            >
                Beta
            </span>
        @endif
    </span>
</a>
