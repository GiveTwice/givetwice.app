@props(['url'])
<p style="margin: 16px 0 0; font-size: 14px; line-height: 1.6;">
    <a href="{{ $url }}" style="display: inline-block; padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 400; text-decoration: underline;">{{ $slot }}</a>
</p>
