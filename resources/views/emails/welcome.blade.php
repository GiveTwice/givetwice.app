@extends('emails.layouts.base')
@section('title', __('Welcome to :app!', ['app' => 'GiveTwice']))
@section('content')
    <x-mail.heading>{{ __('Welcome to :app!', ['app' => 'GiveTwice']) }}</x-mail.heading>
    <p>{{ __('Hi :name,', ['name' => $user->name]) }}</p>
    <p>{{ __("You're in! Your wishlist is ready.") }}</p>
    <x-mail.panel>
        <h2 style="color: #374151; font-size: 16px; line-height: 1.4; font-weight: 600; margin: 0 0 16px;">{{ __("Here's how it works:") }}</h2>
        <div style="margin: 16px 0;">
            <p style="margin: 0; font-weight: 600;">{{ __('1. Add your favorite items') }}</p>
            <p style="margin: 4px 0 0;">{{ __('Paste a link from any store, we grab the details') }}</p>
        </div>
        <div style="margin: 16px 0;">
            <p style="margin: 0; font-weight: 600;">{{ __('2. Share with friends & family') }}</p>
            <p style="margin: 4px 0 0;">{{ __('Send the link however you want, text, email, carrier pigeon') }}</p>
        </div>
        <div style="margin: 16px 0 0;">
            <p style="margin: 0; font-weight: 600;">{{ __('3. Receive perfect gifts') }}</p>
            <p style="margin: 4px 0 0;">{{ __('They claim secretly - no duplicates, no spoiled surprises!') }}</p>
        </div>
    </x-mail.panel>
    <x-mail.action :url="$wishlistUrl" :button="true">{{ __('Go to My Wishlist') }}</x-mail.action>
    <x-mail.quiet-link :url="$faqUrl">{{ __('Check our FAQ') }}</x-mail.quiet-link>
@endsection
