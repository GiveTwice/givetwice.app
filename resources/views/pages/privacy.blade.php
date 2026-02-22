@extends('layouts.app')

@section('title', __('Privacy Policy'))

@section('description', __('meta.privacy'))

@section('content')
<div class="max-w-3xl mx-auto py-8 sm:py-12 lg:py-16">

    <div class="mb-10">
        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ __('Privacy Policy for :app', ['app' => config('app.name')]) }}</h1>
        <p class="text-gray-500">{{ __('Last updated:') }} {{ __('February 21, 2026') }}</p>
    </div>

    <div class="bg-white border border-cream-200 rounded-2xl p-6 lg:p-10 shadow-sm">
        <div class="prose prose-gray max-w-none">
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('1. Information we collect') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-3">{{ __('We collect information you provide directly to us, such as when you create an account, create a wishlist, or contact us for support.') }}</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>{{ __('Account information (name, email address, profile picture)') }}</li>
                    <li>{{ __('Wishlist content (product URLs, titles, descriptions, prices, images)') }}</li>
                    <li>{{ __('Claim information (name and email address for gift claims)') }}</li>
                    <li>{{ __('Preferences (language and currency settings)') }}</li>
                    <li>{{ __('Social login identifiers (if you choose to sign in with Google, Facebook, or Apple)') }}</li>
                    <li>{{ __('Session data (IP address, browser information, last activity timestamp)') }}</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-3">{{ __('Note: Social login via Google, Facebook, or Apple is entirely optional. You can always create an account using just your email address.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('2. How we use your information') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-3">{{ __('We use the information we collect to:') }}</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>{{ __('Provide, maintain, and improve our services') }}</li>
                    <li>{{ __('Send you technical notices and support messages') }}</li>
                    <li>{{ __('Respond to your comments and questions') }}</li>
                    <li>{{ __('Analyze anonymized, aggregated data about claimed gifts (such as which retail platforms are most popular) to identify affiliate program opportunities — this helps us maximize charitable donations') }}</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-3">{{ __('When analyzing gift claim data for affiliate program purposes, we only use anonymized information (e.g., domain names of gift URLs) and never associate this data with individual users.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('3. Data retention periods') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-3">{{ __('We retain your data only for as long as necessary. Specific retention periods are:') }}</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li><strong>{{ __('Account data') }}</strong> — {{ __('retained while your account is active, plus 2 years after your last activity. Inactive accounts are deleted after 24 months (with a warning email sent at 22 months).') }}</li>
                    <li><strong>{{ __('Guest sessions') }}</strong> — {{ __('automatically deleted after 7 days of inactivity.') }}</li>
                    <li><strong>{{ __('Authenticated sessions') }}</strong> — {{ __('automatically deleted after 30 days of inactivity.') }}</li>
                    <li><strong>{{ __('List invitations') }}</strong> — {{ __('expired, accepted, or declined invitations are deleted after 60 days.') }}</li>
                    <li><strong>{{ __('Password reset tokens') }}</strong> — {{ __('automatically deleted after 1 hour.') }}</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('4. Information sharing') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-3">{{ __('We do not sell, trade, or otherwise transfer your personal information to third parties. We may share information in the following circumstances:') }}</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li>{{ __('With your consent') }}</li>
                    <li>{{ __('To comply with legal obligations') }}</li>
                    <li>{{ __('To protect our rights and safety') }}</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-3"><strong>{{ __('Gift claims:') }}</strong> {{ __('When someone claims a gift from your wishlist, you will receive their name and email address so you can coordinate the gift-giving. Similarly, if you claim a gift, your name and email will be shared with the wishlist owner.') }}</p>
                <p class="text-gray-600 leading-relaxed mt-3"><strong>{{ __('Third-party services:') }}</strong> {{ __('If you choose to sign in using Google, Facebook, or Apple, their respective privacy policies apply to the data they collect during authentication. We only receive your basic profile information (name, email, profile picture) from these services.') }}</p>
                <p class="text-gray-600 leading-relaxed mt-3"><strong>{{ __('Subprocessors:') }}</strong> {{ __('We use a limited number of third-party service providers to operate GiveTwice. For a complete list, see our') }} <a href="{{ route('subprocessors', ['locale' => app()->getLocale()]) }}" class="text-coral-600 hover:text-coral-700 underline">{{ __('subprocessors page') }}</a>.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('5. International data transfers') }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ __('Your data is primarily processed within the European Union. Some of our subprocessors may process data outside the EU/EEA. In such cases, we ensure appropriate safeguards are in place, such as Standard Contractual Clauses (SCCs) or adequacy decisions by the European Commission.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('6. Data processing agreements') }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ __('We have Data Processing Agreements (DPAs) in place with all third-party service providers that process personal data on our behalf. These agreements ensure that your data is processed in compliance with the GDPR and only according to our instructions.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('7. Your rights') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-3">{{ __('Under the General Data Protection Regulation (GDPR), you have the following rights:') }}</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li><strong>{{ __('Right of access (Art. 15)') }}</strong> — {{ __('you can request a copy of the personal data we hold about you.') }}</li>
                    <li><strong>{{ __('Right to rectification (Art. 16)') }}</strong> — {{ __('you can update or correct inaccurate personal data via your account settings.') }}</li>
                    <li><strong>{{ __('Right to erasure (Art. 17)') }}</strong> — {{ __('you can delete your account and all associated data from your account settings.') }}</li>
                    <li><strong>{{ __('Right to data portability (Art. 20)') }}</strong> — {{ __('you can export all your personal data in a machine-readable JSON format from your account settings.') }}</li>
                    <li><strong>{{ __('Right to object (Art. 21)') }}</strong> — {{ __('you can object to the processing of your personal data for certain purposes.') }}</li>
                    <li><strong>{{ __('Right to lodge a complaint') }}</strong> — {{ __('you have the right to lodge a complaint with a supervisory authority if you believe your data protection rights have been violated.') }}</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-3">{{ __('To exercise any of these rights, please') }} <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-coral-600 hover:text-coral-700 underline">{{ __('contact us') }}</a>.</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('8. Data security') }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ __('We implement appropriate technical and organizational security measures to protect your personal information, including encrypted connections (HTTPS), secure password hashing, and access controls. However, no method of transmission over the Internet is 100% secure.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('9. Data breach notification') }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ __('In the event of a personal data breach that is likely to result in a risk to your rights and freedoms, we will notify the relevant supervisory authority within 72 hours. If the breach is likely to result in a high risk to you, we will also notify you directly via email.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('10. Cookies') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-3">{{ __('We use only essential cookies that are strictly necessary for the operation of our service:') }}</p>
                <ul class="list-disc pl-6 space-y-1 text-gray-600">
                    <li><strong>{{ __('Session cookie') }}</strong> — {{ __('maintains your login session and remembers your preferences. Expires when you close your browser or after the session lifetime.') }}</li>
                    <li><strong>{{ __('XSRF token') }}</strong> — {{ __('protects against cross-site request forgery attacks. Expires with your session.') }}</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-3">{{ __('We do not use analytics cookies, advertising cookies, or any third-party tracking cookies.') }}</p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('11. Changes to this policy') }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ __('We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date.') }}</p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('12. Contact us') }}</h2>
                <p class="text-gray-600 leading-relaxed">{{ __('If you have any questions about this Privacy Policy or want to exercise your data protection rights, please') }} <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-coral-600 hover:text-coral-700 underline">{{ __('contact us') }}</a>.</p>
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
