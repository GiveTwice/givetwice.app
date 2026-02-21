@extends('layouts.app')

@section('title', __('Subprocessors'))

@section('description', __('meta.subprocessors'))

@section('content')
<div class="max-w-3xl mx-auto py-8 sm:py-12 lg:py-16">

    <div class="mb-10">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl text-2xl mb-6">
            &#128279;
        </div>
        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ __('Subprocessors') }}</h1>
        <p class="text-xl text-gray-600">{{ __('Third-party services that process data on our behalf.') }}</p>
    </div>

    <div class="bg-white border border-cream-200 rounded-2xl p-6 lg:p-10 shadow-sm mb-8">
        <div class="prose prose-gray max-w-none">
            <p class="text-gray-600 leading-relaxed mb-6">{{ __('GiveTwice uses a limited number of third-party service providers ("subprocessors") to help us operate and improve our service. Each subprocessor only has access to the data it needs to perform its specific function.') }}</p>

            <div class="overflow-x-auto -mx-2">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-cream-200">
                            <th class="text-left py-3 px-2 font-semibold text-gray-900">{{ __('Service') }}</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-900">{{ __('Purpose') }}</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-900">{{ __('Data processed') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600">
                        <tr class="border-b border-cream-100">
                            <td class="py-3 px-2 font-medium text-gray-900">DigitalOcean</td>
                            <td class="py-3 px-2">{{ __('Cloud hosting & database') }}</td>
                            <td class="py-3 px-2">{{ __('All application data') }}</td>
                        </tr>
                        <tr class="border-b border-cream-100">
                            <td class="py-3 px-2 font-medium text-gray-900">Cloudflare</td>
                            <td class="py-3 px-2">{{ __('CDN & DDoS protection') }}</td>
                            <td class="py-3 px-2">{{ __('IP addresses, request metadata') }}</td>
                        </tr>
                        <tr class="border-b border-cream-100">
                            <td class="py-3 px-2 font-medium text-gray-900">Laravel Forge</td>
                            <td class="py-3 px-2">{{ __('Server management') }}</td>
                            <td class="py-3 px-2">{{ __('Server access credentials') }}</td>
                        </tr>
                        <tr class="border-b border-cream-100">
                            <td class="py-3 px-2 font-medium text-gray-900">Google OAuth</td>
                            <td class="py-3 px-2">{{ __('Social authentication') }}</td>
                            <td class="py-3 px-2">{{ __('Name, email, profile picture') }}</td>
                        </tr>
                        <tr class="border-b border-cream-100">
                            <td class="py-3 px-2 font-medium text-gray-900">Facebook OAuth</td>
                            <td class="py-3 px-2">{{ __('Social authentication') }}</td>
                            <td class="py-3 px-2">{{ __('Name, email, profile picture') }}</td>
                        </tr>
                        <tr class="border-b border-cream-100">
                            <td class="py-3 px-2 font-medium text-gray-900">Postmark</td>
                            <td class="py-3 px-2">{{ __('Transactional email delivery') }}</td>
                            <td class="py-3 px-2">{{ __('Email addresses, email content') }}</td>
                        </tr>
                        <tr class="border-b border-cream-100">
                            <td class="py-3 px-2 font-medium text-gray-900">Oh Dear</td>
                            <td class="py-3 px-2">{{ __('Uptime & performance monitoring') }}</td>
                            <td class="py-3 px-2">{{ __('Application URLs, response times') }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-2 font-medium text-gray-900">Slack</td>
                            <td class="py-3 px-2">{{ __('Internal error notifications') }}</td>
                            <td class="py-3 px-2">{{ __('Error messages (no personal data)') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-cream-50 to-cream-100 border border-cream-200 rounded-2xl p-6 lg:p-8 text-center">
        <p class="text-gray-600 text-sm mb-2">{{ __('This list was last reviewed on') }} {{ __('February 21, 2026') }}.</p>
        <p class="text-gray-600 text-sm">{{ __('We will update this page when we add or remove subprocessors. For questions, please') }} <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="text-coral-600 hover:text-coral-700 underline">{{ __('contact us') }}</a>.</p>
    </div>

    <div class="mt-10 text-center">
        <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Privacy Policy') }}
        </a>
    </div>
</div>
@endsection
