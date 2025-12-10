@extends('emails.layouts.base')

@section('title', __('Welcome to :app!', ['app' => config('app.name')]))

@section('content')
    <h1 style="color: #16a34a; margin-top: 0;">{{ __('Welcome to :app!', ['app' => config('app.name')]) }}</h1>

    <p>{{ __('Hello') }} {{ $user->name }},</p>

    <p>{{ __('Thank you for joining :app! We\'re excited to have you on board.', ['app' => config('app.name')]) }}</p>

    <p>{{ __('With :app, you can:', ['app' => config('app.name')]) }}</p>

    <ul style="color: #4b5563;">
        <li>{{ __('Create and share your wishlists') }}</li>
        <li>{{ __('Add gifts from any online store') }}</li>
        <li>{{ __('Let friends and family claim gifts without spoiling the surprise') }}</li>
    </ul>

    <p>{{ __('And the best part? All affiliate revenue from purchases made through your lists goes to charity!') }}</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $dashboardUrl }}" style="background: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
            {{ __('Go to Dashboard') }}
        </a>
    </p>

    <p style="color: #666;">{{ __('Happy gifting!') }}</p>
@endsection
