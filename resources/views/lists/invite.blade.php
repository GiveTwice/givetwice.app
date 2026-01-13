@extends('layouts.app')

@section('title', __('Invite collaborators'))

@section('robots', 'noindex, nofollow')

@section('content')
<x-app-content
    :title="__('Invite collaborators')"
    :description="__('Share this list with others so they can add and manage gifts together.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => $list->name, 'url' => url('/' . app()->getLocale() . '/list/' . $list->slug . '/edit')],
        ['label' => __('Invite')]
    ]"
>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <div class="lg:col-span-3">
            <form action="{{ route('lists.invite.store', ['locale' => app()->getLocale(), 'list' => $list->slug]) }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="email" class="form-label">
                        {{ __('Email address') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="{{ __('Enter email address') }}"
                        class="form-input @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="form-help">{{ __('They will receive an email invitation to collaborate on this list.') }}</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ url('/' . app()->getLocale() . '/list/' . $list->slug . '/edit') }}" class="btn-cancel">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn-primary">
                        <x-icons.mail class="w-5 h-5" />
                        {{ __('Send invitation') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-cream-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Collaborators') }}</h2>

                <div class="space-y-3">
                    @foreach($list->users as $user)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                            <div class="flex items-center gap-3">
                                @if($user->hasProfileImage())
                                    <img src="{{ $user->getProfileImageUrl('thumb') }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-coral-400 to-coral-500 flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ $user->getInitials() }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="text-xs text-gray-400 font-normal">({{ __('you') }})</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                            @if($list->users->count() > 1 && $user->id !== auth()->id() && $user->id !== $list->creator_id)
                                <form action="{{ route('lists.collaborator.remove', ['locale' => app()->getLocale(), 'list' => $list->slug, 'user' => $user->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="{{ __('Remove') }}">
                                        <x-icons.close class="w-5 h-5" />
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if($list->pendingInvitations->isNotEmpty())
                <div class="bg-white rounded-xl border border-cream-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Pending invitations') }}</h2>

                    <div class="space-y-3">
                        @foreach($list->pendingInvitations as $invitation)
                            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <x-icons.mail class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">{{ $invitation->email }}</p>
                                        <p class="text-xs text-gray-400">{{ __('Invited :time', ['time' => $invitation->created_at->diffForHumans()]) }}</p>
                                    </div>
                                </div>
                                <form action="{{ route('lists.invitation.cancel', ['locale' => app()->getLocale(), 'invitation' => $invitation->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="{{ __('Cancel invitation') }}">
                                        <x-icons.close class="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-content>
@endsection
