@extends('layouts.app')

@section('title', __('Transparency'))

@section('description', __('meta.transparency'))

@section('content')
<div class="max-w-3xl mx-auto py-8 sm:py-12 lg:py-16">

    <div class="mb-10">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl text-2xl mb-6">
            &#128065;
        </div>
        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ __('Transparency') }}</h1>
        <p class="text-xl text-gray-600">{{ __('Our commitment to openness about how we operate.') }}</p>
    </div>

    {{-- Current Status --}}
    <div class="bg-sunny-50 border border-sunny-200 rounded-2xl p-6 lg:p-8 mb-8">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 bg-sunny-200 text-sunny-700 rounded-xl flex items-center justify-center text-lg">
                &#128679;
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('We\'re just getting started') }}</h2>
                <p class="text-gray-700 leading-relaxed">{{ __('We have been live since December 2025. There is no revenue yet, so nothing to donate. As soon as that changes, you will find it here.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-cream-200 rounded-2xl p-6 lg:p-10 shadow-sm mb-8">
        <div class="prose prose-gray max-w-none">

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Our promise') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('If you later buy through our shop links, all our commission will go to charity. We are setting that up now.') }}</p>
                <p class="text-gray-600 leading-relaxed">{{ __('Once we generate revenue, we will publicly share:') }}</p>
            </section>

            <div class="grid sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-cream-50 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">&#128176;</div>
                    <p class="font-medium text-gray-900 text-sm">{{ __('Revenue & expenses') }}</p>
                    <p class="text-gray-500 text-xs mt-1">{{ __('What we earn and what we spend') }}</p>
                </div>
                <div class="bg-cream-50 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">&#128200;</div>
                    <p class="font-medium text-gray-900 text-sm">{{ __('Commission breakdown') }}</p>
                    <p class="text-gray-500 text-xs mt-1">{{ __('How much goes to charity') }}</p>
                </div>
                <div class="bg-cream-50 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">&#10084;&#65039;</div>
                    <p class="font-medium text-gray-900 text-sm">{{ __('Charities supported') }}</p>
                    <p class="text-gray-500 text-xs mt-1">{{ __('Where the money goes') }}</p>
                </div>
            </div>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Why we can\'t show this yet') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('We have been live since December 2025. There is no revenue yet, so nothing to donate. As soon as that changes, you will find it here.') }}</p>
                <p class="text-gray-600 leading-relaxed">{{ __('We believe it would be misleading to publish empty reports or projections. Instead, we\'ll wait until we have real data to share.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('When to expect updates') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('Once there is revenue, we plan to publish:') }}</p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li>{{ __('Quarterly summaries of revenue, costs, and charitable donations') }}</li>
                    <li>{{ __('Annual reports with detailed breakdowns') }}</li>
                    <li>{{ __('Named charities and donation amounts') }}</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Hold us accountable') }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ __('Our promise is public so you can hold us to it. Future transparency reports will appear right here.') }}</p>
            </section>

        </div>
    </div>

    {{-- Open Source --}}
    <div class="bg-white border border-cream-200 rounded-2xl p-6 lg:p-8 shadow-sm mb-8">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 bg-gray-100 text-gray-700 rounded-xl flex items-center justify-center">
                <x-icons.github class="w-5 h-5" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Fully open source') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('GiveTwice is open source. You can read every line of code on GitHub.') }}</p>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('Want to see how claims work? Check the code. For future donations, check our transparency reports here.') }}</p>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('We built this in public because we don\'t want you to just trust us. Verify it yourself.') }}</p>
                <a href="https://github.com/GiveTwice" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-gray-900 hover:text-coral-600 font-medium transition-colors">
                    <x-icons.github class="w-4 h-4" />
                    {{ __('View our code on GitHub') }}
                    <x-icons.external-link class="w-4 h-4" />
                </a>
            </div>
        </div>
    </div>

    {{-- Coming Soon Notice --}}
    <div class="bg-gradient-to-br from-teal-50 to-teal-100 border border-teal-200 rounded-2xl p-6 lg:p-8 text-center">
        <div class="text-3xl mb-3">&#128202;</div>
        <h3 class="font-semibold text-gray-900 mb-2">{{ __('Reports coming once there is revenue') }}</h3>
        <p class="text-gray-600 text-sm mb-4">{{ __('We have been live since December 2025. There is no revenue yet, so nothing to donate. As soon as that changes, you will find it here.') }}</p>
        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-teal-600 hover:text-teal-700 font-medium text-sm">
            {{ __('Questions? Email us') }} <span class="ml-1">&rarr;</span>
        </a>
    </div>

    <div class="mt-10 text-center">
        <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to About') }}
        </a>
    </div>
</div>
@endsection
