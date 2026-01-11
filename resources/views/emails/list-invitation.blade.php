@extends('emails.layouts.base')

@section('title', __("You're invited to collaborate!", locale: $locale))

@section('content')
    <h1 style="color: #0d9488; margin-top: 0; font-size: 24px;">{{ __("You're invited!", locale: $locale) }} &#127873;</h1>

    <p>{{ __('Hello', locale: $locale) }},</p>

    <p>{{ __(':name has invited you to collaborate on their wishlist:', ['name' => $inviter->name], $locale) }}</p>

    <div style="background: #f0fdfa; padding: 16px; border-radius: 12px; margin: 20px 0; border: 1px solid #99f6e4;">
        <strong style="color: #0d9488;">{{ $list->name }}</strong>
        @if($list->description)
            <br><span style="color: #6b7280; font-size: 14px;">{{ $list->description }}</span>
        @endif
    </div>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __("As a collaborator, you'll be able to add, edit, and manage gifts on this list together.", locale: $locale) }}
    </p>

    @if($isExistingUser)
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $acceptUrl }}" style="background: #14b8a6; color: white; padding: 14px 28px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: 600;">
                {{ __('Accept invitation', locale: $locale) }}
            </a>
        </p>
        <p style="color: #9ca3af; font-size: 13px; text-align: center;">
            {{ __('You can also accept or decline this invitation from your dashboard.', locale: $locale) }}
        </p>
    @else
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $registerUrl }}" style="background: #14b8a6; color: white; padding: 14px 28px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: 600;">
                {{ __('Create account & accept', locale: $locale) }}
            </a>
        </p>
        <p style="color: #9ca3af; font-size: 13px; text-align: center;">
            {{ __('Create a free account to start collaborating.', locale: $locale) }}
        </p>
    @endif

    <p style="color: #9ca3af; font-size: 12px; margin-top: 30px;">
        {{ __('This invitation expires in 30 days.', locale: $locale) }}
    </p>
@endsection
