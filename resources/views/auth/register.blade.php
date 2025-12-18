@extends('layouts.guest')

@section('title', __('Register'))

@section('description', __('meta.register'))

@section('robots', 'noindex, nofollow')

@section('content')
@if(config('app.allow_registration'))
    {{-- Normal registration form --}}
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-cream-200">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-sunny-100 text-sunny-600 rounded-2xl text-2xl mb-4 transform rotate-3">
                &#127873;
            </div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Create your account') }}</h2>
            <p class="text-gray-600 mt-1">{{ __('Start creating wishlists in minutes') }}</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus class="form-input">
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="form-input">
            </div>

            <div class="mb-6">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input type="password" name="password" id="password" required class="form-input">
            </div>

            <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
                {{ __('Create Account') }}
            </button>
        </form>

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-cream-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-3 bg-white text-gray-500">{{ __('Or continue with') }}</span>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <a href="{{ route('auth.google', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center justify-center px-4 py-3 border border-cream-200 rounded-xl hover:bg-cream-50 hover:border-cream-300 transition-colors">
                    <img src="/icons/logo-google.svg" alt="Google" class="w-5 h-5 mr-2">
                    {{ __('Google') }}
                </a>
                <a href="{{ route('auth.facebook', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center justify-center px-4 py-3 border border-cream-200 rounded-xl hover:bg-cream-50 hover:border-cream-300 transition-colors">
                    <img src="/icons/logo-facebook.svg" alt="Facebook" class="w-5 h-5 mr-2">
                    {{ __('Facebook') }}
                </a>
            </div>
        </div>

        <div class="mt-6 text-center">
            <span class="text-gray-600">{{ __('Already have an account?') }}</span>
            <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-coral-600 hover:text-coral-700 font-medium ml-1">
                {{ __('Login') }}
            </a>
        </div>

        <div class="mt-6 pt-6 border-t border-cream-200">
            <p class="text-center text-sm text-gray-500 flex items-center justify-center">
                <span class="text-coral-500 mr-2">&#10084;</span>
                {{ __('All affiliate profits go to charity') }}
            </p>
        </div>
    </div>
@else
    {{-- Coming soon / Registration disabled --}}
    <div class="relative">
        {{-- Decorative floating elements --}}
        <div class="absolute -top-6 -left-6 w-12 h-12 rounded-full bg-coral-100/60 blur-xl coming-soon-float-1"></div>
        <div class="absolute -top-4 -right-8 w-16 h-16 rounded-full bg-sunny-100/50 blur-xl coming-soon-float-2"></div>
        <div class="absolute -bottom-8 -left-4 w-14 h-14 rounded-full bg-teal-100/50 blur-xl coming-soon-float-3"></div>
        <div class="absolute -bottom-6 -right-6 w-10 h-10 rounded-full bg-coral-100/40 blur-xl coming-soon-float-1"></div>

        <div class="relative bg-white p-8 rounded-2xl shadow-sm border border-cream-200 overflow-hidden">
            {{-- Subtle background pattern --}}
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22><circle cx=%2230%22 cy=%2230%22 r=%222%22 fill=%22%23E8614D%22/></svg>');"></div>

            <div class="relative text-center">
                {{-- Animated gift icon with sparkles --}}
                <div class="relative inline-block mb-6">
                    {{-- Sparkles around the icon --}}
                    <div class="absolute -top-2 -left-3 text-sunny-400 coming-soon-sparkle-1">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0L14.59 9.41L24 12L14.59 14.59L12 24L9.41 14.59L0 12L9.41 9.41L12 0Z"/>
                        </svg>
                    </div>
                    <div class="absolute -top-1 -right-4 text-coral-300 coming-soon-sparkle-2">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0L14.59 9.41L24 12L14.59 14.59L12 24L9.41 14.59L0 12L9.41 9.41L12 0Z"/>
                        </svg>
                    </div>
                    <div class="absolute -bottom-1 -left-4 text-teal-300 coming-soon-sparkle-3">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0L14.59 9.41L24 12L14.59 14.59L12 24L9.41 14.59L0 12L9.41 9.41L12 0Z"/>
                        </svg>
                    </div>

                    {{-- Main gift icon --}}
                    <div class="coming-soon-gift inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-sunny-100 via-sunny-50 to-coral-50 rounded-3xl text-4xl shadow-lg shadow-sunny-200/50 border border-sunny-200/50">
                        &#127873;
                    </div>
                </div>

                {{-- Main heading with brand colors --}}
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('Something special is coming') }}
                </h2>

                {{-- Subheading --}}
                <p class="text-gray-600 mb-8 max-w-xs mx-auto leading-relaxed">
                    {{ __("We're putting the finishing touches on GiveTwice. Sign up will be available soon!") }}
                </p>

                {{-- Status indicator --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-cream-50 rounded-full border border-cream-200 mb-8">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                    </span>
                    <span class="text-sm text-gray-600 font-medium">{{ __('Launching soon') }}</span>
                </div>

                {{-- Divider --}}
                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-cream-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-4 bg-white text-gray-400 text-sm">{{ __('Want early access?') }}</span>
                    </div>
                </div>

                {{-- Contact CTA --}}
                <p class="text-gray-600 text-sm mb-4">
                    {{ __('Reach out and let us know why you want to try GiveTwice early.') }}
                </p>

                <a href="{{ url('/' . app()->getLocale() . '/contact') }}"
                   class="inline-flex items-center gap-2 bg-coral-500 text-white px-6 py-3 rounded-xl hover:bg-coral-600 font-semibold transition-all duration-300 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Get in touch') }}
                </a>

                {{-- Already have account link --}}
                <div class="mt-8 pt-6 border-t border-cream-200">
                    <span class="text-gray-600">{{ __('Already have an account?') }}</span>
                    <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-coral-600 hover:text-coral-700 font-medium ml-1 transition-colors">
                        {{ __('Login') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Floating decorative blobs */
        .coming-soon-float-1 {
            animation: comingSoonFloat1 6s ease-in-out infinite;
        }
        .coming-soon-float-2 {
            animation: comingSoonFloat2 8s ease-in-out infinite;
        }
        .coming-soon-float-3 {
            animation: comingSoonFloat3 7s ease-in-out infinite;
        }

        @keyframes comingSoonFloat1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(8px, -12px) scale(1.1); }
        }
        @keyframes comingSoonFloat2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-10px, 8px) scale(1.15); }
        }
        @keyframes comingSoonFloat3 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(6px, 10px) scale(1.05); }
        }

        /* Gift icon gentle float */
        .coming-soon-gift {
            animation: comingSoonGift 4s ease-in-out infinite;
        }

        @keyframes comingSoonGift {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-8px) rotate(2deg); }
        }

        /* Sparkle animations - staggered timing */
        .coming-soon-sparkle-1 {
            animation: comingSoonSparkle 3s ease-in-out infinite;
        }
        .coming-soon-sparkle-2 {
            animation: comingSoonSparkle 3s ease-in-out 1s infinite;
        }
        .coming-soon-sparkle-3 {
            animation: comingSoonSparkle 3s ease-in-out 2s infinite;
        }

        @keyframes comingSoonSparkle {
            0%, 100% { opacity: 0.4; transform: scale(0.8) rotate(0deg); }
            50% { opacity: 1; transform: scale(1.2) rotate(180deg); }
        }
    </style>
@endif
@endsection
