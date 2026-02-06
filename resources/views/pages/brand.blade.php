@extends('layouts.app')

@section('title', __('Brand assets'))

@section('description', __('Download GiveTwice logos, brand colors, and pre-written social media copy for press and partnerships.'))

@section('content')

<div class="text-center py-12 lg:py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-coral-100 text-coral-500 rounded-2xl text-3xl mb-6 transform rotate-3">
        &#127912;
    </div>
    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">{{ __('Brand assets & logos') }}</h1>
    <p class="text-xl text-gray-600 max-w-2xl mx-auto">{{ __('Everything you need to link to or write about GiveTwice.') }}</p>
</div>

<div class="max-w-4xl mx-auto" x-data="brandAssets()">

    {{-- Logos --}}
    <section class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Logos') }}</h2>
        <p class="text-gray-600 mb-8">{{ __('Right-click and save, or use the download links below each logo.') }}</p>

        <div class="grid md:grid-cols-2 gap-8">
            {{-- Heart icon --}}
            <div class="flex flex-col bg-white border border-cream-200 rounded-2xl p-5">
                <p class="font-semibold text-gray-900 mb-3">{{ __('Heart icon') }}</p>
                <div class="bg-cream-50 rounded-xl p-8 flex items-center justify-center mb-4 grow">
                    <img src="{{ asset('images/brand/logo-icon.svg') }}" alt="GiveTwice heart icon" class="w-20 h-20">
                </div>
                <div class="flex gap-2">
                    <a href="{{ asset('images/brand/logo-icon.svg') }}" download class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cream-50 border border-cream-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-cream-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        SVG
                    </a>
                    <a href="{{ asset('images/logo-icon.png') }}" download class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cream-50 border border-cream-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-cream-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        PNG
                    </a>
                </div>
            </div>

            {{-- Full logo --}}
            <div class="flex flex-col bg-white border border-cream-200 rounded-2xl p-5">
                <p class="font-semibold text-gray-900 mb-3">{{ __('Full logo') }}</p>
                <div class="bg-cream-50 rounded-xl p-6 flex items-center justify-center mb-4 grow">
                    <img src="{{ asset('images/brand/logo-full.svg') }}" alt="GiveTwice full logo" class="w-full max-w-[180px]">
                </div>
                <div class="flex gap-2">
                    <a href="{{ asset('images/brand/logo-full.svg') }}" download class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cream-50 border border-cream-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-cream-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        SVG
                    </a>
                    <a href="{{ asset('images/logo-text.png') }}" download class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cream-50 border border-cream-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-cream-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        PNG
                    </a>
                </div>
            </div>
        </div>

        <div class="info-box mt-6">
            <p class="text-sm text-gray-600">{{ __('Please keep proportions intact, don\'t modify the colors, and leave enough whitespace around the logo.') }}</p>
        </div>
    </section>

    {{-- Color palette --}}
    <section class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Color palette') }}</h2>
        <p class="text-gray-600 mb-8">{{ __('Our four brand colors, defined in oklch. Click a value to copy it.') }}</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['name' => 'Coral', 'bg' => 'bg-coral-500', 'hex' => '#D03739', 'desc' => __('Primary, CTAs')],
                ['name' => 'Teal', 'bg' => 'bg-teal-500', 'hex' => '#0FB89A', 'desc' => __('Trust, success')],
                ['name' => 'Sunny', 'bg' => 'bg-sunny-500', 'hex' => '#EBAB00', 'desc' => __('Joy, warmth')],
                ['name' => 'Cream', 'bg' => 'bg-cream-200', 'hex' => '#FEEFDC', 'desc' => __('Backgrounds')],
            ] as $color)
                <div class="bg-white border border-cream-200 rounded-xl overflow-hidden">
                    <div class="h-24 {{ $color['bg'] }}"></div>
                    <div class="p-4">
                        <p class="font-semibold text-gray-900 mb-1">{{ $color['name'] }}</p>
                        <p class="text-sm text-gray-500 mb-2">{{ $color['desc'] }}</p>
                        <button @click="copyColor('{{ $color['hex'] }}')" class="text-xs font-mono text-gray-600 hover:text-gray-900 transition-colors cursor-pointer">{{ $color['hex'] }}</button>
                        <p
                            class="text-xs text-teal-600 mt-1 transition-opacity duration-150"
                            :class="copiedColor === '{{ $color['hex'] }}' ? 'opacity-100' : 'opacity-0'"
                        >&nbsp;{{ __('Copied!') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Typography --}}
    <section class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Typography') }}</h2>
        <p class="text-gray-600 mb-6">{{ __('We use Instrument Sans from Google Fonts.') }}</p>

        <div class="bg-white border border-cream-200 rounded-xl p-6">
            <div class="mb-4">
                <span class="text-3xl font-bold text-gray-900">Give</span><span class="text-3xl font-bold text-coral-500">Twice</span>
            </div>
            <p class="text-sm text-gray-500">{{ __('"Give" in dark gray (#111827), "Twice" in coral (#D03739). Always bold.') }}</p>
            <div class="mt-4 pt-4 border-t border-cream-100">
                <a href="https://fonts.google.com/specimen/Instrument+Sans" target="_blank" rel="noopener noreferrer" class="text-sm text-coral-600 hover:text-coral-700 font-medium">
                    Instrument Sans on Google Fonts &rarr;
                </a>
            </div>
        </div>
    </section>

    {{-- Tagline --}}
    <section class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Tagline') }}</h2>

        <div class="space-y-3">
            <div class="bg-white border border-cream-200 rounded-xl p-5 flex items-center justify-between gap-4">
                <p class="text-gray-900 font-medium">{{ __('One gift. Two smiles. Zero catch.') }}</p>
                <div class="shrink-0 flex items-center gap-2">
                    <span class="text-xs text-teal-600 transition-opacity duration-150" :class="copiedSnippet === 'tagline1' ? 'opacity-100' : 'opacity-0'">{{ __('Copied!') }}</span>
                    <button @click="copySnippet({{ json_encode(__('One gift. Two smiles. Zero catch.')) }}, 'tagline1')" class="text-gray-400 hover:text-coral-500 transition-colors cursor-pointer" :title="'{{ __('Copy') }}'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" /></svg>
                    </button>
                </div>
            </div>
            <div class="bg-white border border-cream-200 rounded-xl p-5 flex items-center justify-between gap-4">
                <p class="text-gray-900 font-medium">{{ __('All affiliate profits go to charity.') }}</p>
                <div class="shrink-0 flex items-center gap-2">
                    <span class="text-xs text-teal-600 transition-opacity duration-150" :class="copiedSnippet === 'tagline2' ? 'opacity-100' : 'opacity-0'">{{ __('Copied!') }}</span>
                    <button @click="copySnippet({{ json_encode(__('All affiliate profits go to charity.')) }}, 'tagline2')" class="text-gray-400 hover:text-coral-500 transition-colors cursor-pointer" :title="'{{ __('Copy') }}'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Social media snippets --}}
    <section class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Social media copy') }}</h2>
        <p class="text-gray-600 mb-8">{{ __('Ready-to-post snippets. Click the copy button to grab one.') }}</p>

        <div class="space-y-6">
            {{-- Twitter / X --}}
            <div>
                <p class="font-semibold text-gray-900 mb-3">Twitter / X</p>
                <div class="space-y-3">
                    @php
                        $tweets = [
                            __('Been using GiveTwice for my wishlists. Free, no ads, and they donate all affiliate profits to charity.') . ' ' . url('/'),
                            __('GiveTwice is a wishlist app that donates 100% of its profits. No tracking, no ads, just gifts that help twice.') . ' ' . url('/'),
                        ];
                    @endphp
                    @foreach($tweets as $tweet)
                        <div class="bg-white border border-cream-200 rounded-xl p-4 flex items-start justify-between gap-4">
                            <p class="text-sm text-gray-700">{{ $tweet }}</p>
                            <div class="shrink-0 mt-0.5 flex items-center gap-2">
                                <span class="text-xs text-teal-600 transition-opacity duration-150" :class="copiedSnippet === 'tweet{{ $loop->index }}' ? 'opacity-100' : 'opacity-0'">{{ __('Copied!') }}</span>
                                <button @click="copySnippet({{ json_encode($tweet) }}, 'tweet{{ $loop->index }}')" class="text-gray-400 hover:text-coral-500 transition-colors cursor-pointer" :title="'{{ __('Copy') }}'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" /></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- LinkedIn --}}
            <div>
                <p class="font-semibold text-gray-900 mb-3">LinkedIn</p>
                <div class="space-y-3">
                    @php
                        $linkedinPosts = [
                            __('I\'ve been using GiveTwice, a free wishlist app that donates 100% of its affiliate profits to charity. You share a list, people buy gifts through it, and the commissions go to good causes. No tracking, no ads.') . "\n\n" . url('/'),
                        ];
                    @endphp
                    @foreach($linkedinPosts as $post)
                        <div class="bg-white border border-cream-200 rounded-xl p-4 flex items-start justify-between gap-4">
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $post }}</p>
                            <div class="shrink-0 mt-0.5 flex items-center gap-2">
                                <span class="text-xs text-teal-600 transition-opacity duration-150" :class="copiedSnippet === 'linkedin{{ $loop->index }}' ? 'opacity-100' : 'opacity-0'">{{ __('Copied!') }}</span>
                                <button @click="copySnippet({{ json_encode($post) }}, 'linkedin{{ $loop->index }}')" class="text-gray-400 hover:text-coral-500 transition-colors cursor-pointer" :title="'{{ __('Copy') }}'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" /></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Back link --}}
    <div class="text-center pb-16">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Home') }}
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script>
    function brandAssets() {
        return {
            copiedColor: null,
            colorTimeout: null,
            copiedSnippet: null,
            snippetTimeout: null,
            copyColor(hex) {
                navigator.clipboard.writeText(hex).then(() => {
                    clearTimeout(this.colorTimeout);
                    this.copiedColor = hex;
                    this.colorTimeout = setTimeout(() => this.copiedColor = null, 2000);
                });
            },
            copySnippet(text, id) {
                navigator.clipboard.writeText(text).then(() => {
                    clearTimeout(this.snippetTimeout);
                    this.copiedSnippet = id;
                    this.snippetTimeout = setTimeout(() => this.copiedSnippet = null, 2000);
                });
            }
        };
    }
</script>
@endpush
