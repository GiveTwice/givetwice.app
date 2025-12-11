<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #374151; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #fefdfb;">
    <div style="background: white; padding: 32px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f3f0eb;">
        @yield('content')
    </div>

    <div style="text-align: center; margin-top: 30px; padding: 20px;">
        <p style="color: #9ca3af; font-size: 13px; margin: 0;">
            {{ __('All affiliate revenue goes to charity.') }}
        </p>
        <p style="color: #d1d5db; font-size: 12px; margin: 8px 0 0 0;">
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
