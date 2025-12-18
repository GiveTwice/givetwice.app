<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dev Preview' }} - GiveTwice</title>
    @vite(['resources/css/app.css'])
    @if($styles ?? false)
    <style>
        {{ $styles }}
    </style>
    @endif
</head>
<body class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-10">
    <div class="mb-2 text-center text-gray-500 text-sm">
        {{ $instructions }}
    </div>
    <div class="mb-4 text-xs text-gray-400 font-mono">{{ $dimensions ?? '' }}</div>
    {{ $slot }}
</body>
</html>
