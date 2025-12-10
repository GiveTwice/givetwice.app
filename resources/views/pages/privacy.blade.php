@extends('layouts.app')

@section('title', __('Privacy Policy'))

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Privacy Policy for :app', ['app' => config('app.name')]) }}</h1>
    <p class="text-gray-500 mb-8">{{ __('Last updated:') }} {{ now()->format('F j, Y') }}</p>

    <div class="prose prose-lg text-gray-600 space-y-6">
        <h2 class="text-xl font-semibold text-gray-900">1. Information We Collect</h2>
        <p>We collect information you provide directly to us, such as when you create an account, create a wishlist, or contact us for support.</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Account information (name, email address)</li>
            <li>Wishlist content (product URLs, titles, descriptions)</li>
            <li>Claim information (email address for anonymous claims)</li>
        </ul>

        <h2 class="text-xl font-semibold text-gray-900">2. How We Use Your Information</h2>
        <p>We use the information we collect to:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Provide, maintain, and improve our services</li>
            <li>Send you technical notices and support messages</li>
            <li>Respond to your comments and questions</li>
        </ul>

        <h2 class="text-xl font-semibold text-gray-900">3. Information Sharing</h2>
        <p>We do not sell, trade, or otherwise transfer your personal information to third parties. We may share information in the following circumstances:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>With your consent</li>
            <li>To comply with legal obligations</li>
            <li>To protect our rights and safety</li>
        </ul>

        <h2 class="text-xl font-semibold text-gray-900">4. Data Security</h2>
        <p>We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.</p>

        <h2 class="text-xl font-semibold text-gray-900">5. Your Rights</h2>
        <p>You have the right to access, correct, or delete your personal information. Contact us to exercise these rights.</p>

        <h2 class="text-xl font-semibold text-gray-900">6. Cookies</h2>
        <p>We use cookies to maintain your session and remember your preferences. You can disable cookies in your browser settings.</p>

        <h2 class="text-xl font-semibold text-gray-900">7. Changes to This Policy</h2>
        <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>

        <h2 class="text-xl font-semibold text-gray-900">8. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-green-600 hover:text-green-700">contact us</a>.</p>
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="text-green-600 hover:text-green-700">
            &larr; {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
