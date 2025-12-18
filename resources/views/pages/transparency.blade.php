@extends('layouts.app')

@section('title', __('Transparency'))

@section('description', __('meta.transparency'))

@section('content')
<div class="max-w-3xl mx-auto py-12 lg:py-16">

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
                <p class="text-gray-700 leading-relaxed">{{ __('GiveTwice is a new project, aiming to launch in early 2026. We don\'t have a track record yet — no revenue, no profits, and therefore no donations to report. We\'re building this from scratch, with the goal of making gift-giving more meaningful.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-cream-200 rounded-2xl p-6 lg:p-10 shadow-sm mb-8">
        <div class="prose prose-gray max-w-none">

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Our promise') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('We\'ve built GiveTwice around a simple idea: 100% of our affiliate profits go to charity. But promises are easy to make — we believe in proving them.') }}</p>
                <p class="text-gray-600 leading-relaxed">{{ __('Once GiveTwice is live and generating revenue, we commit to publicly sharing:') }}</p>
            </section>

            <div class="grid sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-cream-50 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">&#128176;</div>
                    <p class="font-medium text-gray-900 text-sm">{{ __('Revenue & expenses') }}</p>
                    <p class="text-gray-500 text-xs mt-1">{{ __('What we earn and what we spend') }}</p>
                </div>
                <div class="bg-cream-50 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">&#128200;</div>
                    <p class="font-medium text-gray-900 text-sm">{{ __('Profit breakdown') }}</p>
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
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('Transparency reports require actual numbers. Since we haven\'t launched yet, there\'s nothing to report — no affiliate revenue has been generated, no operating costs have been incurred beyond initial development, and no charitable donations have been made.') }}</p>
                <p class="text-gray-600 leading-relaxed">{{ __('We believe it would be misleading to publish empty reports or projections. Instead, we\'ll wait until we have real data to share.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('When to expect updates') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('Once GiveTwice launches and the idea proves useful and popular, we\'ll begin publishing regular transparency reports. We\'re aiming for:') }}</p>
                <ul class="list-disc pl-6 space-y-2 text-gray-600">
                    <li>{{ __('Quarterly summaries of revenue, costs, and charitable donations') }}</li>
                    <li>{{ __('Annual reports with detailed breakdowns') }}</li>
                    <li>{{ __('Named charities and donation amounts') }}</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Hold us accountable') }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ __('We\'re publishing this page now — before we launch — to hold ourselves accountable. When the time comes, you\'ll find our transparency reports right here. If we\'re not living up to our promises, we want you to call us out.') }}</p>
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
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('GiveTwice is completely open source. Every line of code is publicly available for anyone to inspect, verify, and contribute to. No secrets, no hidden logic — just honest software that does exactly what we say it does.') }}</p>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('This is our ultimate form of transparency. You don\'t have to take our word for how the app works — you can see for yourself.') }}</p>
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
        <h3 class="font-semibold text-gray-900 mb-2">{{ __('Transparency reports coming soon') }}</h3>
        <p class="text-gray-600 text-sm mb-4">{{ __('Check back after our launch in early 2026 for our first transparency report.') }}</p>
        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-teal-600 hover:text-teal-700 font-medium text-sm">
            {{ __('Questions? Get in touch') }} <span class="ml-1">&rarr;</span>
        </a>
    </div>

    <div class="mt-10 text-center">
        <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to About') }}
        </a>
    </div>
</div>
@endsection
