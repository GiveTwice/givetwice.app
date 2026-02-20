@extends('layouts.app')

@section('title', __('Terms of Service'))

@section('description', __('meta.terms'))

@section('content')
<div class="max-w-3xl mx-auto py-8 sm:py-12 lg:py-16">

    <div class="mb-10">
        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ __('Terms of Service for :app', ['app' => config('app.name')]) }}</h1>
        <p class="text-gray-500">{{ __('Last updated:') }} December 15, 2025</p>
    </div>

    <div class="bg-white border border-cream-200 rounded-2xl p-6 lg:p-10 shadow-sm">
        <div class="prose prose-gray max-w-none">
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">1. Acceptance of Terms</h2>
                <p class="text-gray-600 leading-relaxed">By accessing and using {{ config('app.name') }}, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our service.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">2. Description of Service</h2>
                <p class="text-gray-600 leading-relaxed">{{ config('app.name') }} provides a wishlist platform that allows users to create, share, and manage gift lists. We also facilitate the claiming of gifts by friends and family.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">3. User Accounts</h2>
                <p class="text-gray-600 leading-relaxed mb-3">To use certain features, you may need to create an account. You can register using your email address or, optionally, sign in with Google or Facebook. You are responsible for:</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>Maintaining the confidentiality of your account</li>
                    <li>All activities that occur under your account</li>
                    <li>Providing accurate and complete information</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">4. User Content</h2>
                <p class="text-gray-600 leading-relaxed mb-3">You retain ownership of content you submit to our service, including gift descriptions, images, and wishlist information. By submitting content, you grant us a license to use, display, and distribute that content in connection with our service.</p>
                <p class="text-gray-600 leading-relaxed">When uploading images, you must ensure you have the right to use them and that they do not contain inappropriate, offensive, or copyrighted material without permission.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">5. Prohibited Uses</h2>
                <p class="text-gray-600 leading-relaxed mb-3">You agree not to:</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>Use the service for any unlawful purpose</li>
                    <li>Attempt to gain unauthorized access to our systems</li>
                    <li>Interfere with or disrupt the service</li>
                    <li>Upload malicious content or spam</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">6. Email Communications</h2>
                <p class="text-gray-600 leading-relaxed mb-3">By creating an account, you agree to receive transactional emails related to your use of the service, including:</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>Welcome emails upon registration</li>
                    <li>Email verification requests</li>
                    <li>Notifications when someone claims a gift from your wishlist</li>
                    <li>Claim confirmation emails (for anonymous claims)</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-3">These are service-related communications necessary for the operation of your account and are not marketing emails.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">7. Affiliate links and charitable donations</h2>
                <p class="text-gray-600 leading-relaxed mb-3">Our service may contain affiliate links. When you make a purchase through these links, we may receive a commission from the retailer at no additional cost to you.</p>
                <p class="text-gray-600 leading-relaxed mb-3">We donate 100% of our affiliate profits to charity. "Profits" means the affiliate revenue remaining after we cover essential operating costs (server infrastructure, required software subscriptions, and any legal expenses). We operate as lean as possible to maximize charitable impact, and our team donates their time to build and maintain this service.</p>
                <p class="text-gray-600 leading-relaxed">To maximize charitable donations, we periodically analyze anonymized gift claim data (without any user information) to identify which retail platforms are most popular. This helps us determine which affiliate programs to join.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">8. Disclaimer of Warranties</h2>
                <p class="text-gray-600 leading-relaxed">The service is provided "as is" without warranties of any kind. We do not guarantee that the service will be uninterrupted or error-free.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">9. Limitation of Liability</h2>
                <p class="text-gray-600 leading-relaxed">We shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the service.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">10. Changes to Terms</h2>
                <p class="text-gray-600 leading-relaxed">We reserve the right to modify these terms at any time. Continued use of the service after changes constitutes acceptance of the new terms.</p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900 mb-3">11. Contact</h2>
                <p class="text-gray-600 leading-relaxed">If you have any questions about these Terms of Service, please <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-coral-600 hover:text-coral-700 underline">contact us</a>.</p>
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
