@extends('emails.layouts.base')

@section('title', __('Welcome to :app!', ['app' => config('app.name')]))

@section('content')
    {{-- Header with coral accent --}}
    <h1 style="color: #E8614D; margin-top: 0; font-size: 24px;">{{ __('Welcome to :app!', ['app' => config('app.name')]) }}</h1>

    <p style="font-size: 16px; color: #374151;">{{ __('Hi :name,', ['name' => $user->name]) }}</p>

    <p style="font-size: 16px; color: #374151;">{{ __('Your wishlist is ready and waiting for you!') }}</p>

    {{-- Primary CTA Button --}}
    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $wishlistUrl }}" style="background: #E8614D; color: white; padding: 14px 28px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: 600; font-size: 16px;">
            {{ __('Go to My Wishlist') }} &#127873;
        </a>
    </p>

    {{-- Getting Started Section --}}
    <div style="background: #FEF7F5; border-radius: 12px; padding: 24px; margin: 30px 0; border: 1px solid #FECDC7;">
        <h2 style="color: #374151; font-size: 18px; margin-top: 0; margin-bottom: 20px;">{{ __('Get started in 3 easy steps:') }}</h2>

        {{-- Step 1: Create (coral) --}}
        <table style="width: 100%; margin-bottom: 16px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 40px; vertical-align: top;">
                    <div style="width: 32px; height: 32px; background: #FECDC7; border-radius: 8px; text-align: center; line-height: 32px; font-size: 16px;">
                        &#127873;
                    </div>
                </td>
                <td style="vertical-align: top; padding-left: 12px;">
                    <p style="margin: 0; font-weight: 600; color: #374151;">{{ __('1. Add your favorite items') }}</p>
                    <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">{{ __('Paste product URLs from any online store') }}</p>
                </td>
            </tr>
        </table>

        {{-- Step 2: Share (sunny/yellow) --}}
        <table style="width: 100%; margin-bottom: 16px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 40px; vertical-align: top;">
                    <div style="width: 32px; height: 32px; background: #FEF0C7; border-radius: 8px; text-align: center; line-height: 32px; font-size: 16px;">
                        &#128279;
                    </div>
                </td>
                <td style="vertical-align: top; padding-left: 12px;">
                    <p style="margin: 0; font-weight: 600; color: #374151;">{{ __('2. Share with friends & family') }}</p>
                    <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">{{ __('Send your wishlist link via email, chat, or social media') }}</p>
                </td>
            </tr>
        </table>

        {{-- Step 3: Receive (teal) --}}
        <table style="width: 100%; margin-bottom: 0;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 40px; vertical-align: top;">
                    <div style="width: 32px; height: 32px; background: #CCFBF1; border-radius: 8px; text-align: center; line-height: 32px; font-size: 16px;">
                        &#10003;
                    </div>
                </td>
                <td style="vertical-align: top; padding-left: 12px;">
                    <p style="margin: 0; font-weight: 600; color: #374151;">{{ __('3. Receive perfect gifts') }}</p>
                    <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 14px;">{{ __('They claim secretly - no duplicates, no spoiled surprises!') }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- FAQ Link --}}
    <p style="color: #6b7280; font-size: 14px;">
        {{ __('Questions or need help?') }} <a href="{{ $faqUrl }}" style="color: #E8614D; text-decoration: none; font-weight: 500;">{{ __('Check our FAQ') }}</a>
    </p>

    {{-- Sign-off --}}
    <p style="color: #374151; margin-top: 24px;">{{ __('Happy gifting!') }}</p>
    <p style="color: #E8614D; margin: 0; font-weight: 500;">{{ config('app.name') }}</p>
@endsection
