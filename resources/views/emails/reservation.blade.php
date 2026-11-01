@extends('emails.layouts.base')
@section('title', __('Reserved for you'))
@section('content')
    <x-mail.heading>{{ __('Reserved for you') }}</x-mail.heading>
    <x-mail.panel>
        <h2 style="color: #0f766e; font-size: 16px; line-height: 1.4; font-weight: 600; margin: 0 0 16px;">{{ $gift->title }}</h2>
        @if($gift->hasImage())<img src="{{ $gift->getImageUrl('thumb') }}" alt="" width="80">@endif
        <p>{{ $gift->formatPrice() }} {{ $gift->siteName() }}</p>
    </x-mail.panel>
    <p>{{ __('Nobody else will buy this now. The surprise is safe. You still buy it yourself in the shop.') }}</p>
    @if($gift->url)
        <x-mail.action :url="$gift->url" :button="true">{{ __('Go to the shop') }}</x-mail.action>
    @else
        <p>{{ __('Buy it wherever you like.') }}</p>
    @endif
    <x-mail.quiet-link :url="$listUrl">{{ __('All my reservations') }}</x-mail.quiet-link>
    <x-mail.quiet-link :url="$undoUrl">{{ __('Undo reservation') }}</x-mail.quiet-link>
    <p style="color: #6b7280; font-size: 14px;">{{ __('The gift will be free again.') }}</p>
@endsection
