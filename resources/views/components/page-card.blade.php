@props(['title' => null, 'subtitle' => null, 'icon' => null])

{{-- One white card for single-purpose pages (a form, a confirmation, a notice), styled like the sign-in card.
     Every one of these pages has the same 512px width, so the width is not a prop. See the layout rule in CLAUDE.md. --}}
<div class="mx-auto w-full max-w-lg sm:pt-4 wrap-anywhere">
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-cream-200 bg-white p-6 shadow-sm sm:p-8']) }}>
        @if($title)
            <div class="mb-6 flex items-center gap-4">
                @if($icon)
                    <div class="flex h-12 w-12 shrink-0 -rotate-3 items-center justify-center rounded-xl bg-coral-100 text-2xl">{{ $icon }}</div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
        @endif
        {{ $slot }}
    </div>
</div>
