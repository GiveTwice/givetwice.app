@extends('emails.layouts.base')

@section('title', __('Updates from your friends\' wishlists'))

@section('content')
    <h1 style="color: #0d9488; margin-top: 0; font-size: 24px;">{{ __('Wishlist updates') }} &#127873;</h1>

    <p>{{ __('Hello') }} {{ $user->name }},</p>

    <p>{{ __('Here\'s what\'s new on your friends\' wishlists:') }}</p>

    @foreach($digestData as $data)
        <div style="background: #f0fdfa; padding: 16px; border-radius: 12px; margin: 20px 0; border: 1px solid #99f6e4;">
            <div style="margin-bottom: 12px;">
                <a href="{{ $data['list']->getPublicUrl($locale) }}" style="color: #0d9488; text-decoration: none;">
                    <strong style="font-size: 16px;">{{ $data['list']->name }}</strong>
                </a>
                <br>
                <span style="color: #6b7280; font-size: 13px;">{{ __('by') }} {{ $data['list']->creator->name }}</span>
            </div>

            @if($data['added_gifts']->isNotEmpty())
                <div style="margin-bottom: 8px;">
                    <span style="color: #059669; font-weight: 600; font-size: 13px;">+ {{ __('Added') }}</span>
                    <ul style="margin: 4px 0 0 0; padding-left: 20px; color: #374151; font-size: 14px;">
                        @foreach($data['added_gifts'] as $gift)
                            <li>{{ $gift->title ?: __('Untitled gift') }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($data['removed_gifts']->isNotEmpty())
                <div>
                    <span style="color: #dc2626; font-weight: 600; font-size: 13px;">- {{ __('Removed') }}</span>
                    <ul style="margin: 4px 0 0 0; padding-left: 20px; color: #6b7280; font-size: 14px;">
                        @foreach($data['removed_gifts'] as $gift)
                            <li style="text-decoration: line-through;">{{ $gift->title ?: __('Untitled gift') }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p style="margin: 12px 0 0 0;">
                <a href="{{ $data['list']->getPublicUrl($locale) }}" style="color: #0d9488; font-size: 13px; font-weight: 500;">
                    {{ __('View wishlist') }} &rarr;
                </a>
            </p>
        </div>
    @endforeach

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $friendsUrl }}" style="background: #0d9488; color: white; padding: 14px 28px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: 600;">
            {{ __('View all friends\' wishlists') }}
        </a>
    </p>

    <p style="color: #9ca3af; font-size: 12px; text-align: center; margin-top: 30px;">
        {{ __('You can manage your notification preferences in') }}
        <a href="{{ $settingsUrl }}#notifications" style="color: #0d9488;">{{ __('Settings') }}</a>.
    </p>
@endsection
