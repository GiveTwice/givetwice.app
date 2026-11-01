@props(['list', 'gift', 'first' => false, 'last' => false])
<form method="POST" action="{{ route('gifts.order', ['locale' => app()->getLocale(), 'list' => $list, 'gift' => $gift]) }}" class="mt-2 grid grid-cols-2 gap-2">
    @csrf
    @foreach(['up' => $first, 'down' => $last] as $direction => $disabled)
        <button name="direction" value="{{ $direction }}" @disabled($disabled) class="btn-secondary min-h-11 justify-center px-1 text-sm disabled:opacity-40" aria-label="{{ __($direction === 'up' ? 'Move :gift up' : 'Move :gift down', ['gift' => $gift->title]) }}">{{ __($direction === 'up' ? 'Move up' : 'Move down') }}</button>
    @endforeach
</form>
