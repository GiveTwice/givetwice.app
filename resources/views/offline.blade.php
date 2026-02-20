<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Offline | {{ config('app.name', 'GiveTwice') }}</title>
    <meta name="robots" content="noindex">

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#f97066">

    @vite(['resources/css/app.css'])

    {{-- Inline fallback styles in case Vite assets aren't cached --}}
    <style>
        .offline-fallback { font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif; }
        .offline-fallback body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    </style>
</head>
<body class="offline-fallback min-h-screen bg-gradient-warm flex flex-col items-center justify-center px-4 safe-area-x">
    <div class="text-center max-w-md mx-auto">
        {{-- Heart icon --}}
        <div class="mb-6">
            <svg class="w-16 h-16 mx-auto opacity-60" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                    <radialGradient id="heart-offline" cx="63.4096" cy="-3.0014" r="76.7353" gradientTransform="matrix(.9985 0 0 .9337 .0985 4.2468)" gradientUnits="userSpaceOnUse">
                        <stop offset=".4748" stop-color="#e53935"/>
                        <stop offset=".913" stop-color="#c62828"/>
                    </radialGradient>
                </defs>
                <path fill="url(#heart-offline)" d="M93.92 9.08c-21.92 0-29.97 26.2-29.97 26.2S56 9.08 33.92 9.08C17.33 9.08-1.59 22.23 5.4 52.11c6.99 29.89 58.33 66.97 58.33 66.97s.26-.1.26-.27c0 .16.17.25.17.25S115.43 82 122.42 52.11c6.99-29.88-11.91-43.03-28.5-43.03z"/>
                <path fill="#424242" d="M93.92 12.08c8.44 0 16.38 3.67 21.25 9.81 5.67 7.15 7.16 17.37 4.32 29.55-5.99 25.62-47.08 57.67-55.56 64.06-8.55-6.44-49.63-38.47-55.62-64.06-2.83-12.18-1.34-22.39 4.33-29.54 4.88-6.15 12.83-9.82 21.28-9.82 19.55 0 27.08 23.84 27.16 24.08l2.84 9.36 2.9-9.34c.07-.25 7.69-24.1 27.1-24.1m0-3c-21.92 0-29.97 26.2-29.97 26.2S56 9.08 33.92 9.08C17.33 9.08-1.59 22.23 5.4 52.11c6.99 29.89 58.33 66.97 58.33 66.97s.26-.1.26-.27c0 .16.17.25.17.25S115.43 82 122.42 52.11c6.99-29.88-11.91-43.03-28.5-43.03z" opacity=".2"/>
            </svg>
        </div>

        {{-- Heading --}}
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">You're offline</h1>

        {{-- Message --}}
        <p class="text-gray-600 text-base sm:text-lg mb-8">
            It looks like you've lost your internet connection. Your recently viewed lists may still be available.
        </p>

        {{-- Retry button --}}
        <button
            onclick="window.location.reload()"
            class="btn-primary inline-flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Try again
        </button>

        {{-- Subtle branding --}}
        <p class="mt-12 text-sm text-gray-400">
            <span class="font-bold text-gray-500">Give</span><span class="font-bold text-coral-400">Twice</span>
        </p>
    </div>

    <div class="safe-area-bottom"></div>
</body>
</html>
