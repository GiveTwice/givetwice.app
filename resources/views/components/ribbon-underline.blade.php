{{-- Ties its text up like a present: one hand-drawn ribbon stroke under it, with a small hook where the pen doubled back, that runs into a bow after the last letter. Both take the text colour of the class it is given. --}}
<span {{ $attributes->merge(['class' => 'relative mr-8 inline-block pb-2.5']) }}>
    {{ $slot }}
    {{-- Stretched to the title plus 17px, so the stroke ends on the bow's knot. The stroke keeps its thickness however wide it gets. --}}
    <svg class="pointer-events-none absolute left-0 -bottom-[3px] h-4 w-[calc(100%+17px)] overflow-visible" viewBox="0 0 300 16" preserveAspectRatio="none" aria-hidden="true">
        <path d="M3 11 C 50 8, 100 7, 133 8 C 146 8.5, 147 12.5, 132 12.5 C 170 13, 240 12, 297 7" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
    </svg>
    <svg class="pointer-events-none absolute -right-8 -bottom-1 h-5 w-[30px] overflow-visible" viewBox="0 0 30 20" aria-hidden="true">
        <path d="M15 10 C 9 2, 2 3, 3 9 C 4 15, 10 13, 15 10 Z M15 10 C 21 2, 28 3, 27 9 C 26 15, 20 13, 15 10 Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
        <path d="M15 10 L 11 19 M15 10 L 19 19" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
        <circle cx="15" cy="10" r="2.6" fill="currentColor"/>
    </svg>
</span>
