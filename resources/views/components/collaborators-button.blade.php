@props(['list', 'max' => 3, 'showLabel' => false, 'quiet' => false])

@php
    $users = $list->users;
    $isShared = $users->count() > 1;
@endphp

<a
    href="{{ route('lists.invite', ['locale' => app()->getLocale(), 'list' => $list->slug]) }}"
    {{ $attributes->merge(['class' => $quiet ? 'inline-flex min-h-11 items-center gap-2 py-2 text-sm text-gray-600 hover:text-gray-900' : 'btn-secondary inline-flex items-center gap-2']) }}
    title="{{ __('Manage collaborators') }}"
>
    @if($isShared)
        <x-avatar-stack :users="$users" :max="$max" />
    @else
        <x-icons.user-plus class="w-5 h-5" />
    @endif
    @if($showLabel)<span>{{ __('Manage together') }}</span>@endif
</a>
