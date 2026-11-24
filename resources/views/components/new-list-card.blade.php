<a
    href="{{ route('lists.create', ['locale' => app()->getLocale()]) }}"
    class="group flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-coral-300 bg-white p-6 text-center transition-colors duration-150 hover:border-coral-500 hover:bg-coral-50"
>
    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-sunny-300 text-sunny-900 shadow-sm">
        <x-icons.plus class="h-5 w-5" />
    </span>
    <span class="text-base font-semibold text-gray-900 group-hover:text-coral-600">{{ __('Another list?') }}</span>
    <span class="text-sm text-gray-500">{{ __('One for your birthday, one for the holidays, one for the kids.') }}</span>
</a>
