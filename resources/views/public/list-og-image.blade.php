@php
    $ogGiftCount = $gifts->total();
    $ogOwner = $list->creator->name;
    $ogAvailableGifts = $gifts->filter(fn($gift) => $gift->claims->isEmpty() || $gift->allow_multiple_claims)->take(3);
@endphp

<div style="width:1200px;height:630px;position:relative;overflow:hidden;background:linear-gradient(135deg,#fef9f0 0%,#fdf3e3 50%,#fef0e8 100%);font-family:-apple-system,'Helvetica Neue',Arial,sans-serif;">
        {{-- Decorative blobs --}}
        <div style="position:absolute;width:500px;height:500px;top:-180px;right:-120px;border-radius:50%;background:radial-gradient(circle,rgba(245,214,128,0.45) 0%,transparent 70%);"></div>
        <div style="position:absolute;width:380px;height:380px;bottom:-140px;left:-80px;border-radius:50%;background:radial-gradient(circle,rgba(45,159,147,0.2) 0%,transparent 70%);"></div>
        <div style="position:absolute;width:220px;height:220px;top:55%;right:14%;border-radius:50%;background:radial-gradient(circle,rgba(240,112,96,0.18) 0%,transparent 70%);"></div>
        {{-- Floating gifts --}}
        <span style="position:absolute;top:70px;right:110px;font-size:44px;opacity:0.12;transform:rotate(-12deg);">🎁</span>
        <span style="position:absolute;bottom:90px;right:190px;font-size:34px;opacity:0.12;transform:rotate(9deg);">🎁</span>
        {{-- Content --}}
        <div style="position:relative;z-index:10;height:100%;display:flex;flex-direction:column;justify-content:center;padding:70px 96px;">
            {{-- Logo --}}
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:36px;">
                <svg width="52" height="52" viewBox="0 0 52 52" fill="none"><path d="M26 44S6 32 6 18.5C6 13.25 10.25 9 15.5 9c3.25 0 6.1 1.6 8 4.1C25.4 10.6 28.25 9 31.5 9 36.75 9 41 13.25 41 18.5 41 32 26 44 26 44z" fill="#f07060"/></svg>
                <span style="font-size:52px;font-weight:800;letter-spacing:-0.02em;line-height:1;"><span style="color:#111827;">Give</span><span style="color:#f07060;">Twice</span></span>
            </div>
            {{-- Headline --}}
            <div style="font-size:52px;font-weight:800;letter-spacing:-0.02em;line-height:1.15;color:#1f2937;margin-bottom:10px;">{{ $list->name }}</div>
            <p style="font-size:26px;color:#6b7280;margin-bottom:32px;">{{ __('By :name', ['name' => $ogOwner]) }}</p>
            {{-- Charity pill --}}
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(45,159,147,0.15);color:#1a7a72;font-size:20px;font-weight:700;padding:10px 22px;border-radius:100px;width:fit-content;">♥ &nbsp;{{ __('og.charity_pill') }}</div>
        </div>
        {{-- Visual card --}}
        <div style="position:absolute;right:56px;top:50%;transform:translateY(-50%) rotate(2.5deg);width:420px;background:#fff;border-radius:28px;padding:36px 32px;box-shadow:0 28px 56px -10px rgba(0,0,0,0.14),0 0 0 1px rgba(220,210,196,0.4);z-index:20;">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
                <span style="font-size:40px;">🎁</span>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#111827;">{{ Str::limit($list->name, 28) }}</div>
                    <div style="font-size:15px;color:#9ca3af;margin-top:2px;">{{ trans_choice(':count gift|:count gifts', $ogGiftCount, ['count' => $ogGiftCount]) }}</div>
                </div>
            </div>
            @foreach($ogAvailableGifts as $ogGift)
                <div style="display:flex;align-items:center;gap:16px;padding:14px 0;{{ $loop->last ? '' : 'border-bottom:1px solid #f3f0ea;' }}">
                    @if($ogGift->hasImage())
                        <img src="{{ $ogGift->getImageUrl('thumb') }}" style="width:60px;height:60px;border-radius:14px;object-fit:cover;flex-shrink:0;" />
                    @else
                        <div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,#f3f0ea,#e5e1d8);display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;">🎁</div>
                    @endif
                    <div style="flex:1;overflow:hidden;">
                        <div style="font-size:17px;font-weight:600;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ogGift->title ?? __('Gift') }}</div>
                        @if($ogGift->hasPrice())
                            <div style="font-size:15px;font-weight:700;color:#f07060;margin-top:2px;">{{ $ogGift->formatPrice() }}</div>
                        @endif
                    </div>
                    <span style="font-size:13px;font-weight:600;padding:5px 14px;border-radius:100px;background:#ccfbf1;color:#0f766e;flex-shrink:0;">{{ __('Available') }}</span>
                </div>
            @endforeach
        </div>
        <div style="position:absolute;bottom:38px;right:56px;font-size:20px;font-weight:600;color:#d1d5db;letter-spacing:0.02em;">givetwice.app</div>
    </div>
