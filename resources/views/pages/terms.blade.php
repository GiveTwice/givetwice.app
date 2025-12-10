@extends('layouts.app')

@section('title', __('Terms of Service'))

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Terms of Service for :app', ['app' => config('app.name')]) }}</h1>
    <p class="text-gray-500 mb-8">{{ __('Last updated:') }} {{ now()->format('F j, Y') }}</p>

    <div class="prose prose-lg text-gray-600 space-y-6">
        <h2 class="text-xl font-semibold text-gray-900">1. Acceptance of Terms</h2>
        <p>By accessing and using {{ config('app.name') }}, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our service.</p>

        <h2 class="text-xl font-semibold text-gray-900">2. Description of Service</h2>
        <p>{{ config('app.name') }} provides a wishlist platform that allows users to create, share, and manage gift lists. We also facilitate the claiming of gifts by friends and family.</p>

        <h2 class="text-xl font-semibold text-gray-900">3. User Accounts</h2>
        <p>To use certain features, you may need to create an account. You are responsible for:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Maintaining the confidentiality of your account</li>
            <li>All activities that occur under your account</li>
            <li>Providing accurate and complete information</li>
        </ul>

        <h2 class="text-xl font-semibold text-gray-900">4. User Content</h2>
        <p>You retain ownership of content you submit to our service. By submitting content, you grant us a license to use, display, and distribute that content in connection with our service.</p>

        <h2 class="text-xl font-semibold text-gray-900">5. Prohibited Uses</h2>
        <p>You agree not to:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Use the service for any unlawful purpose</li>
            <li>Attempt to gain unauthorized access to our systems</li>
            <li>Interfere with or disrupt the service</li>
            <li>Upload malicious content or spam</li>
        </ul>

        <h2 class="text-xl font-semibold text-gray-900">6. Affiliate Links</h2>
        <p>Our service may contain affiliate links. When you make a purchase through these links, we may receive a commission. All affiliate revenue is donated to charity.</p>

        <h2 class="text-xl font-semibold text-gray-900">7. Disclaimer of Warranties</h2>
        <p>The service is provided "as is" without warranties of any kind. We do not guarantee that the service will be uninterrupted or error-free.</p>

        <h2 class="text-xl font-semibold text-gray-900">8. Limitation of Liability</h2>
        <p>We shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the service.</p>

        <h2 class="text-xl font-semibold text-gray-900">9. Changes to Terms</h2>
        <p>We reserve the right to modify these terms at any time. Continued use of the service after changes constitutes acceptance of the new terms.</p>

        <h2 class="text-xl font-semibold text-gray-900">10. Contact</h2>
        <p>If you have any questions about these Terms of Service, please <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-green-600 hover:text-green-700">contact us</a>.</p>
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="text-green-600 hover:text-green-700">
            &larr; {{ __('Back to Home') }}
        </a>
    </div>
</div>
@endsection
