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
        <div class="flex items-center -space-x-1.5">
            @foreach($users->take($max) as $user)
                @if($user->hasProfileImage())
                    <img
                        src="{{ $user->getProfileImageUrl('thumb') }}"
                        alt="{{ $user->name }}"
                        class="w-5 h-5 rounded-full ring-1 ring-white object-cover"
                    >
                @else
                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-coral-400 to-coral-500 flex items-center justify-center ring-1 ring-white">
                        <span class="text-white font-bold text-[8px]">{{ $user->getInitials() }}</span>
                    </div>
                @endif
            @endforeach
            @if($users->count() > $max)
                <div class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center ring-1 ring-white">
                    <span class="text-gray-600 font-bold text-[8px]">+{{ $users->count() - $max }}</span>
                </div>
            @endif
        </div>
    @else
        <x-icons.user-plus class="w-5 h-5" />
    @endif
    @if($showLabel)<span>{{ __('Manage together') }}</span>@endif
</a>
