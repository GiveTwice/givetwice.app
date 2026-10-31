@extends('emails.layouts.base')
@section('title', __('Reserved for you'))
@section('content')
    <h1>{{ __('Reserved for you') }}</h1>
    @if($gift->hasImage())<img src="{{ $gift->getImageUrl('thumb') }}" alt="" width="80">@endif
    <h2>{{ $gift->title }}</h2>
    <p>{{ $gift->formatPrice() }} {{ $gift->siteName() }}</p>
    <p>{{ __('Nobody else will buy this now. The surprise is safe. You still buy it yourself in the shop.') }}</p>
    @if($gift->url)<p><a href="{{ $gift->url }}">{{ __('Go to the shop') }}</a></p>@else<p>{{ __('Buy it wherever you like.') }}</p>@endif
    <p><a href="{{ $listUrl }}">{{ __('All my reservations') }}</a></p>
    <p><a href="{{ $undoUrl }}">{{ __('Undo reservation') }}</a>. {{ __('The gift will be free again.') }}</p>
@endsection
