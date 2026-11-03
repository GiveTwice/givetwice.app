@extends('emails.layouts.base')

@section('title', __('Wishlist updates'))

@section('content')
    <x-mail.heading>{{ __('Wishlist updates') }}</x-mail.heading>
    <p>{{ __('Hello') }} {{ $user->name }},</p>
    <p>{{ __('list-updates.intro') }}</p>

    @foreach($digestData as $data)
        <x-mail.panel>
            <h2 style="color: #0f766e; font-size: 16px; line-height: 1.4; font-weight: 600; margin: 0 0 4px;">{{ str_replace("\u{2014}", '·', $data['list']->name) }}</h2>
            <p style="color: #6b7280; font-size: 14px; margin: 0 0 16px;">{{ __('list-updates.actors', ['names' => $data['actors'] ?? $data['list']->creator->name]) }}</p>
            @if($first = $data['added_gifts']->first())
                @if($first->gift?->hasImage())
                    <img src="{{ $first->gift->getImageUrl('thumb') }}" alt="" width="80" style="border-radius: 8px;">
                @endif
            @endif
            <x-mail.change-section :label="__('Added')" :gifts="$data['added_gifts']->take(1)" />
            @if($data['added_gifts']->count() > 1)
                <p>{{ __('list-updates.more', ['count' => $data['added_gifts']->count() - 1]) }}</p>
            @endif
            @foreach([__('list-updates.edited') => $data['edited_gifts'] ?? collect(), __('Removed') => $data['removed_gifts']] as $label => $gifts)
                <x-mail.change-section :label="$label" :gifts="$gifts->take(1)" />
                @if($gifts->count() > 1)
                    <p>{{ __('list-updates.more', ['count' => $gifts->count() - 1]) }}</p>
                @endif
            @endforeach
            <x-mail.action :url="$data['list']->getPublicUrl($locale)" :button="true">{{ __('list-updates.view') }}</x-mail.action>
            @isset($data['mute_url'])
                <x-mail.quiet-link :url="$data['mute_url']">{{ __('list-updates.stop') }}</x-mail.quiet-link>
            @endisset
        </x-mail.panel>
    @endforeach

    <x-mail.quiet-link :url="$settingsUrl.'#notifications'">{{ __('list-updates.settings') }}</x-mail.quiet-link>
@endsection
