{{-- The lang attribute puts the locale in the template, so every locale gets its own image hash. --}}
<div lang="{{ app()->getLocale() }}" style="width:1200px;height:630px;position:relative;overflow:hidden;background:linear-gradient(180deg,oklch(0.96 0.04 50) 0%,oklch(0.98 0.02 70) 50%,oklch(0.99 0.01 80) 100%);font-family:ui-sans-serif,system-ui,sans-serif;">
    {{-- Decorative circles --}}
    <div style="position:absolute;width:500px;height:500px;top:-150px;right:-100px;border-radius:50%;opacity:0.5;background:radial-gradient(circle,oklch(0.94 0.11 95 / 60%) 0%,transparent 70%);"></div>
    <div style="position:absolute;width:400px;height:400px;bottom:-120px;left:-80px;border-radius:50%;opacity:0.5;background:radial-gradient(circle,oklch(0.94 0.05 175 / 40%) 0%,transparent 70%);"></div>
    <div style="position:absolute;width:200px;height:200px;top:60%;right:15%;border-radius:50%;opacity:0.5;background:radial-gradient(circle,oklch(0.94 0.04 25 / 50%) 0%,transparent 70%);"></div>
    {{-- Floating gifts --}}
    <span style="position:absolute;top:80px;right:120px;font-size:48px;opacity:0.15;transform:rotate(-15deg);">🎁</span>
    <span style="position:absolute;bottom:100px;right:200px;font-size:36px;opacity:0.15;transform:rotate(10deg);">🎁</span>
    {{-- Content --}}
    <div style="position:relative;z-index:10;height:100%;display:flex;flex-direction:column;justify-content:center;padding:70px 100px;">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:36px;">
            <svg width="72" height="72" viewBox="0 0 128 128" aria-hidden="true"><path fill="#e53935" d="M93.92 9.08c-21.92 0-29.97 26.2-29.97 26.2S56 9.08 33.92 9.08C17.33 9.08-1.59 22.23 5.4 52.11c6.99 29.89 58.33 66.97 58.33 66.97s.26-.1.26-.27c0 .16.17.25.17.25S115.43 82 122.42 52.11c6.99-29.88-11.91-43.03-28.5-43.03z"/></svg>
            <span style="font-size:64px;font-weight:700;letter-spacing:-0.02em;line-height:1;"><span style="color:#111827;">Give</span><span style="color:oklch(0.57 0.19 25);">Twice</span></span>
        </div>
        <div style="font-size:42px;font-weight:600;line-height:1.3;max-width:640px;color:#374151;margin-bottom:28px;">
            {{ __('Your wishlist.') }}<br>
            <span style="color:oklch(0.57 0.19 25);">{{ __('Less guesswork.') }}</span>
        </div>
        <p style="font-size:24px;line-height:1.5;max-width:620px;color:#6b7280;">{{ __('Make a wishlist. Share it with friends and family. Leave the guessing to someone else.') }}</p>
        <div style="display:flex;gap:16px;margin-top:40px;">
            <span style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:100px;font-size:18px;font-weight:600;background:oklch(0.94 0.04 25);color:oklch(0.50 0.16 25);">🎁 {{ __('No duplicate gifts') }}</span>
            <span style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:100px;font-size:18px;font-weight:600;background:oklch(0.94 0.05 175);color:oklch(0.48 0.10 175);">♥ {{ __('Free. No ads.') }}</span>
        </div>
    </div>
    {{-- Example list --}}
    <div style="position:absolute;right:72px;top:50%;transform:translateY(-50%) rotate(3deg);width:360px;background:#fff;border-radius:24px;padding:28px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.15),0 0 0 1px oklch(0.93 0.04 70 / 50%);z-index:20;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <span style="font-size:32px;">🎁</span>
            <div>
                <div style="font-size:18px;font-weight:700;color:#111827;">{{ __('Birthday wishlist') }}</div>
                <div style="font-size:14px;color:#9ca3af;">{{ trans_choice(':count gift idea|:count gift ideas', 3, ['count' => 3]) }}</div>
            </div>
        </div>
        @foreach([['🎧', __('Headphones'), '€ 79', 'oklch(0.93 0.03 250)', true], ['🧣', __('Cozy Blanket'), '€ 45', 'oklch(0.95 0.06 85)', false], ['📚', __('Book Set'), '€ 32', 'oklch(0.94 0.05 160)', true]] as [$emoji, $title, $price, $tint, $available])
            <div style="display:flex;align-items:center;gap:12px;padding:12px 0;{{ $loop->last ? '' : 'border-bottom:1px solid oklch(0.96 0.03 75);' }}">
                <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;background:{{ $tint }};flex-shrink:0;">{{ $emoji }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:600;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $title }}</div>
                    <div style="font-size:14px;font-weight:700;color:oklch(0.57 0.19 25);">{{ $price }}</div>
                </div>
                <span style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:100px;white-space:nowrap;{{ $available ? 'background:oklch(0.94 0.05 175);color:oklch(0.48 0.10 175);' : 'background:oklch(0.97 0.05 95);color:oklch(0.48 0.11 65);' }}">{{ $available ? __('Available') : __('Claimed') }}</span>
            </div>
        @endforeach
    </div>
    <div style="position:absolute;bottom:40px;right:60px;font-size:22px;font-weight:600;letter-spacing:0.02em;color:#9ca3af;">givetwice.app</div>
</div>
