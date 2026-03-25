<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Redirecting...') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #faf9f7; color: #374151; }
        .wrap { text-align: center; padding: 2rem; }
        .spinner { width: 24px; height: 24px; border: 3px solid #e5e7eb; border-top-color: #0d9488; border-radius: 50%; animation: spin .6s linear infinite; margin: 0 auto 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        a { color: #0d9488; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="spinner"></div>
        <p>{{ __('Redirecting to :store...', ['store' => $storeName]) }}</p>
        <p><a href="{{ $fallbackUrl }}">{{ __('Click here if not redirected') }}</a></p>
    </div>
    <script>
        (function() {
            var affiliate = @json($affiliateUrl);
            var fallback = @json($fallbackUrl);

            try {
                var probeDomain = new URL(affiliate).origin;
            } catch(e) {
                window.location.replace(fallback);
                return;
            }

            if (affiliate === fallback) {
                window.location.replace(fallback);
                return;
            }

            fetch(probeDomain + '/robots.txt', { mode: 'no-cors' })
                .then(function() { window.location.replace(affiliate); })
                .catch(function() { window.location.replace(fallback); });

            setTimeout(function() { window.location.replace(fallback); }, 3000);
        })();
    </script>
    <noscript>
        <meta http-equiv="refresh" content="0;url={{ $fallbackUrl }}">
    </noscript>
</body>
</html>
