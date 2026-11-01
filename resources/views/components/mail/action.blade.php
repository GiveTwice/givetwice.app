@props(['url', 'button' => false])
<p style="margin: 20px 0; {{ $button ? 'text-align: center;' : '' }}">
    <a href="{{ $url }}" style="color: {{ $button ? '#ffffff' : '#0f766e' }}; font-size: 15px; font-weight: 600; line-height: 1.6; text-decoration: {{ $button ? 'none' : 'underline' }}; {{ $button ? 'display: inline-block; background: #0f766e; padding: 14px 24px; border-radius: 12px;' : 'display: inline-block; padding: 8px 0;' }}">{{ $slot }}</a>
</p>
