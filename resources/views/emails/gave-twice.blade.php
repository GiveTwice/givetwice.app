@extends('emails.layouts.base')
@section('title', __('Your gift is reserved'))
@section('content')
    <x-mail.heading>{{ __('Great, this gift is reserved for you.') }}</x-mail.heading>
    <p>{{ $giftTitle }}: {{ __('Remember to buy the gift at the store.') }}</p>
    <x-mail.panel>
        <p style="margin: 0;">{{ __('If you later buy through our shop links, all our commission will go to charity. We are setting that up now.') }}</p>
        <x-mail.quiet-link :url="route('transparency', ['locale' => app()->getLocale()])">{{ __('Transparency') }}</x-mail.quiet-link>
    </x-mail.panel>
    <x-mail.action :url="config('app.url').'?utm_source=givetwice&utm_medium=email&utm_campaign=gave-twice'" :button="true">{{ __('Make your own wishlist') }}</x-mail.action>
@endsection
