{{-- Ties its text up like a present: a stitched ribbon line under it and a small bow after the last letter, both in the text colour of the class it is given. --}}
<span {{ $attributes->merge(['class' => "relative mr-8 inline-block pb-2.5 after:pointer-events-none after:absolute after:inset-x-0 after:bottom-[3px] after:border-b-[3px] after:border-dashed after:border-current after:content-['']"]) }}>
    {{ $slot }}
    <svg class="pointer-events-none absolute -right-8 -bottom-1 h-5 w-[30px] overflow-visible" viewBox="0 0 30 20" aria-hidden="true">
        <path d="M15 10 C 9 2, 2 3, 3 9 C 4 15, 10 13, 15 10 Z M15 10 C 21 2, 28 3, 27 9 C 26 15, 20 13, 15 10 Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
        <path d="M15 10 L 11 19 M15 10 L 19 19" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
        <circle cx="15" cy="10" r="2.6" fill="currentColor"/>
    </svg>
</span>
