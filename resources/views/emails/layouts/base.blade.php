<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GiveTwice')</title>
    <meta name="color-scheme" content="light dark">
    <style>
        @media (prefers-color-scheme: dark) {
            body { background-color: #101827 !important; color: #e5e7eb !important; }
            .mail-content { background: #1c2635 !important; border-color: #344155 !important; }
            .mail-panel { background: #142f30 !important; border-color: #365c59 !important; }
            h1, h2, a { color: #82e0cf !important; }
            h3, p, li, strong, span { color: #d1d5db !important; }
            a[style*="background: #0f766e"] { background: #115e59 !important; color: white !important; }
            a[style*="color: #6b7280"] { color: #b4becc !important; }
        }
    </style>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #374151; max-width: 600px; margin: 0 auto; padding: 16px; background-color: #FFFBF8;">
    <div class="mail-content" style="background: white; padding: 24px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #F5EDE8;">
        @yield('content')
    </div>

    <div style="text-align: center; margin-top: 24px; padding: 16px;">
        <p style="color: #6b7280; font-size: 13px; margin: 0;">
            {{ __('Free. No ads.') }}
        </p>
        <p style="color: #b64032; font-size: 14px; margin: 10px 0 0 0; font-weight: 500; letter-spacing: 0.5px;">
            GiveTwice ❤️
        </p>
    </div>
</body>
</html>
