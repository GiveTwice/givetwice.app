@extends('emails.layouts.base')

@section('title', __('Your GiveTwice account will be deleted soon'))

@section('content')
    <h1 style="color: #E8614D; margin-top: 0; font-size: 24px;">{{ __('Your account will be deleted soon') }}</h1>

    <p style="font-size: 16px; color: #374151;">{{ __('Hi :name,', ['name' => $user->name]) }}</p>

    <p style="font-size: 16px; color: #374151;">{{ __('We noticed you haven\'t used your GiveTwice account in a while. To protect your privacy, we automatically delete inactive accounts after 24 months.') }}</p>

    <div style="background: #FEF7F5; border-radius: 12px; padding: 24px; margin: 30px 0; border: 1px solid #FECDC7;">
        <p style="margin: 0; color: #374151; font-weight: 600;">{{ __('What happens next?') }}</p>
        <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 15px;">{{ __('If you don\'t log in within the next 2 months, your account and all associated data will be permanently deleted.') }}</p>
    </div>

    <p style="font-size: 15px; color: #374151;">{{ __('Want to keep your account? Simply log in and your account will stay active.') }}</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $loginUrl }}" style="background: #E8614D; color: white; padding: 14px 28px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: 600; font-size: 16px;">
            {{ __('Log in to keep your account') }}
        </a>
    </p>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __('You can also export your data before deletion from your') }} <a href="{{ $exportUrl }}" style="color: #E8614D; text-decoration: none; font-weight: 500;">{{ __('settings page') }}</a>.
    </p>
@endsection
