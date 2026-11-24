{{-- Draws a hand-drawn marker swoosh under its text, in the current text colour of the class it is given. --}}
<span {{ $attributes->merge(['class' => 'relative inline-block pb-2.5']) }}>
    {{ $slot }}
    <svg class="pointer-events-none absolute inset-x-0 -bottom-0.5 h-3.5 w-full overflow-visible" viewBox="0 0 300 14" preserveAspectRatio="none" aria-hidden="true">
        <path d="M4 9 C 55 4, 120 3, 175 6 S 262 9, 296 3" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
        <path d="M70 12 C 130 9, 200 9, 258 11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" opacity="0.55" vector-effect="non-scaling-stroke"/>
    </svg>
</span>
