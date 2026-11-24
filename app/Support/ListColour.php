<?php

namespace App\Support;

/**
 * Gives each of a user's lists the next brand colour, like a stack of differently wrapped presents.
 * The colour follows the list's position in the user's lists, oldest first, so the dashboard card,
 * the underline under the list page's title and the sidebar dot of one list always match.
 *
 * The card shades are not redefined for dark mode in app.css, so cards look the same in both modes.
 * That is also why the "+N" tile uses bg-[#fff]/15 and not bg-white/15: app.css restyles every
 * bg-white/* class in dark mode. app.css lists this file as a Tailwind source, so these classes are built.
 */
class ListColour
{
    /**
     * @return array{card: string, muted: string, empty: string, more: string, underline: string, dot: string}
     */
    public static function forPosition(int $position): array
    {
        return [
            [
                'card' => 'bg-coral-500 text-white shadow-[0_12px_28px_-12px_oklch(0.57_0.19_25/0.75)]',
                'muted' => 'text-white/80',
                'empty' => 'border-white/50',
                'more' => 'bg-[#fff]/15 ring-white/60 text-white',
                'underline' => 'text-coral-500',
                'dot' => 'bg-coral-500',
            ],
            [
                'card' => 'bg-teal-700 text-white shadow-[0_12px_28px_-12px_oklch(0.48_0.10_175/0.8)]',
                'muted' => 'text-white/80',
                'empty' => 'border-white/50',
                'more' => 'bg-[#fff]/15 ring-white/60 text-white',
                'underline' => 'text-teal-600',
                'dot' => 'bg-teal-700',
            ],
            [
                'card' => 'bg-sunny-300 text-sunny-900 shadow-[0_12px_28px_-12px_oklch(0.78_0.18_85/0.9)]',
                'muted' => 'text-sunny-900/80',
                'empty' => 'border-sunny-700/40',
                'more' => 'bg-sunny-900/10 ring-sunny-900/30 text-sunny-900',
                'underline' => 'text-sunny-400',
                'dot' => 'bg-sunny-400',
            ],
        ][$position % 3];
    }
}
