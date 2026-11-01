@extends('emails.layouts.base')

@section('title', __('Wishlist updates'))

@section('content')
    <x-mail.heading>{{ __('Wishlist updates') }}</x-mail.heading>
    <p>{{ __('Hello') }} {{ $user->name }},</p>
    <p>{{ __('list-updates.intro') }}</p>

    @foreach($digestData as $data)
        <x-mail.panel>
            <h2 style="color: #0f766e; font-size: 16px; line-height: 1.4; font-weight: 600; margin: 0 0 4px;">{{ str_replace("\u{2014}", '·', $data['list']->name) }}</h2>
            <p style="color: #6b7280; font-size: 14px; margin: 0 0 16px;">{{ __('by') }} {{ $data['list']->creator->name }}</p>
            <x-mail.change-section :label="__('Added')" :gifts="$data['added_gifts']" />
            <x-mail.change-section :label="__('list-updates.edited')" :gifts="$data['edited_gifts'] ?? collect()" />
            <x-mail.change-section :label="__('Removed')" :gifts="$data['removed_gifts']" />
            <x-mail.action :url="$data['list']->getPublicUrl($locale)">{{ __('list-updates.view') }}</x-mail.action>
            @isset($data['mute_url'])
                <x-mail.quiet-link :url="$data['mute_url']">{{ __('list-updates.stop') }}</x-mail.quiet-link>
            @endisset
        </x-mail.panel>
    @endforeach

    <x-mail.quiet-link :url="$settingsUrl.'#notifications'">{{ __('list-updates.settings') }}</x-mail.quiet-link>
@endsection
