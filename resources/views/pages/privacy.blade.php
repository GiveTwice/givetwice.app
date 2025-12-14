@extends('layouts.app')

@section('title', __('Privacy Policy'))

@section('content')
<div class="max-w-3xl mx-auto py-12 lg:py-16">

    <div class="mb-10">
        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ __('Privacy Policy for :app', ['app' => config('app.name')]) }}</h1>
        <p class="text-gray-500">{{ __('Last updated:') }} {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="bg-white border border-cream-200 rounded-2xl p-6 lg:p-10 shadow-sm">
        <div class="prose prose-gray max-w-none">
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">1. Information We Collect</h2>
                <p class="text-gray-600 leading-relaxed mb-3">We collect information you provide directly to us, such as when you create an account, create a wishlist, or contact us for support.</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>Account information (name, email address)</li>
                    <li>Wishlist content (product URLs, titles, descriptions)</li>
                    <li>Claim information (email address for anonymous claims)</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">2. How We Use Your Information</h2>
                <p class="text-gray-600 leading-relaxed mb-3">We use the information we collect to:</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>Provide, maintain, and improve our services</li>
                    <li>Send you technical notices and support messages</li>
                    <li>Respond to your comments and questions</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">3. Information Sharing</h2>
                <p class="text-gray-600 leading-relaxed mb-3">We do not sell, trade, or otherwise transfer your personal information to third parties. We may share information in the following circumstances:</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>With your consent</li>
                    <li>To comply with legal obligations</li>
                    <li>To protect our rights and safety</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">4. Data Security</h2>
                <p class="text-gray-600 leading-relaxed">We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">5. Your Rights</h2>
                <p class="text-gray-600 leading-relaxed">You have the right to access, correct, or delete your personal information. Contact us to exercise these rights.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">6. Cookies</h2>
                <p class="text-gray-600 leading-relaxed">We use cookies to maintain your session and remember your preferences. You can disable cookies in your browser settings.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">7. Changes to This Policy</h2>
                <p class="text-gray-600 leading-relaxed">We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900 mb-3">8. Contact Us</h2>
                <p class="text-gray-600 leading-relaxed">If you have any questions about this Privacy Policy, please <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-coral-600 hover:text-coral-700 underline">contact us</a>.</p>
            </section>
        </div>
    </div>

    <div class="mt-10 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
