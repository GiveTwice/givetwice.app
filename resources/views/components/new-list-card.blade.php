<a
    href="{{ route('lists.create', ['locale' => app()->getLocale()]) }}"
    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-cream-300 p-6 text-center transition-colors duration-150 hover:border-coral-300 hover:bg-cream-50/70"
>
    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-coral-100 text-coral-600">
        <x-icons.plus class="h-5 w-5" />
    </span>
    <span class="text-base font-semibold text-gray-900 group-hover:text-coral-600">{{ __('Another list?') }}</span>
    <span class="text-sm text-gray-500">{{ __('One for your birthday, one for the holidays, one for the kids.') }}</span>
</a>
