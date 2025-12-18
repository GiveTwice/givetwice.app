<x-dev.layouts.base>
    <x-slot:title>OG Image Preview</x-slot:title>

    <x-slot:dimensions>1200 × 630</x-slot:dimensions>

    <x-slot:instructions>
        Screenshot the canvas. Save as <code>public/images/og-image.png</code>
    </x-slot:instructions>

    <x-slot:styles>
        /* Canvas size */
        .canvas {
            width: 1200px;
            height: 630px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.5;
        }

        .deco-circle-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, oklch(0.94 0.11 95 / 0.6) 0%, transparent 70%);
            top: -150px;
            right: -100px;
        }

        .deco-circle-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, oklch(0.94 0.05 175 / 0.4) 0%, transparent 70%);
            bottom: -120px;
            left: -80px;
        }

        .deco-circle-3 {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, oklch(0.94 0.04 25 / 0.5) 0%, transparent 70%);
            top: 60%;
            right: 15%;
        }

        /* Floating gift icons */
        .gift-icon {
            position: absolute;
            font-size: 48px;
            opacity: 0.15;
        }

        .gift-1 { top: 80px; right: 120px; transform: rotate(-15deg); }
        .gift-2 { bottom: 100px; right: 200px; transform: rotate(10deg); font-size: 36px; }
        .gift-3 { top: 180px; right: 280px; transform: rotate(5deg); font-size: 32px; opacity: 0.1; }

        /* Main content */
        .content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 100px;
        }

        /* Logo section */
        .logo {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 40px;
        }

        .logo .heart-svg {
            width: 72px;
            height: 72px;
            flex-shrink: 0;
        }

        .logo-text {
            font-size: 64px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Small inline heart for badges */
        .badge .heart-svg-inline {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            margin-right: 4px;
        }

        /* Tagline */
        .tagline {
            font-size: 42px;
            font-weight: 600;
            color: var(--gray-700);
            line-height: 1.3;
            max-width: 700px;
            margin-bottom: 32px;
        }

        .tagline-highlight {
            color: var(--coral-500);
        }

        /* Subtitle */
        .subtitle {
            font-size: 24px;
            color: var(--gray-500);
            max-width: 600px;
            line-height: 1.5;
        }

        /* Badges */
        .badges {
            display: flex;
            gap: 16px;
            margin-top: 48px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 100px;
            font-size: 18px;
            font-weight: 600;
        }

        .badge-coral {
            background: var(--coral-100);
            color: oklch(0.50 0.16 25);
        }

        .badge-teal {
            background: var(--teal-100);
            color: oklch(0.48 0.10 175);
        }

        .badge-sunny {
            background: var(--sunny-100);
            color: oklch(0.58 0.14 70);
        }

        /* Domain */
        .domain {
            position: absolute;
            bottom: 40px;
            right: 60px;
            font-size: 22px;
            font-weight: 600;
            color: var(--gray-400);
            letter-spacing: 0.02em;
        }

        /* Visual card */
        .visual-card {
            position: absolute;
            right: 80px;
            top: 50%;
            transform: translateY(-50%) rotate(3deg);
            width: 280px;
            background: white;
            border-radius: 24px;
            padding: 28px;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.15),
                0 0 0 1px oklch(0.93 0.04 70 / 0.5);
        }

        .visual-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .visual-card-icon {
            font-size: 32px;
        }

        .visual-card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .visual-card-subtitle {
            font-size: 13px;
            color: var(--gray-400);
        }

        .visual-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid oklch(0.96 0.03 75);
        }

        .visual-item:last-child {
            border-bottom: none;
        }

        .visual-item-emoji {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .visual-item-emoji-1 { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
        .visual-item-emoji-2 { background: linear-gradient(135deg, #fef3c7, #fde68a); }
        .visual-item-emoji-3 { background: linear-gradient(135deg, #d1fae5, #a7f3d0); }

        .visual-item-text {
            flex: 1;
        }

        .visual-item-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .visual-item-price {
            font-size: 13px;
            font-weight: 700;
            color: var(--coral-500);
        }

        .visual-item-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 100px;
        }

        .item-badge-available {
            background: var(--teal-100);
            color: oklch(0.48 0.10 175);
        }

        .item-badge-claimed {
            background: var(--sunny-100);
            color: oklch(0.58 0.14 70);
        }
    </x-slot:styles>

    <div class="canvas bg-gradient-warm">
            {{-- Decorative circles --}}
            <div class="deco-circle deco-circle-1"></div>
            <div class="deco-circle deco-circle-2"></div>
            <div class="deco-circle deco-circle-3"></div>

            {{-- Floating gift icons --}}
            <span class="gift-icon gift-1">&#127873;</span>
            <span class="gift-icon gift-2">&#127873;</span>
            <span class="gift-icon gift-3">&#127873;</span>

            {{-- Main content --}}
            <div class="content">
                {{-- Logo --}}
                <div class="logo">
                    <x-heart-icon class="heart-svg" />
                    <span class="logo-text">
                        <span class="text-gray-900">Give</span><span class="text-coral-500">Twice</span>
                    </span>
                </div>

                {{-- Tagline --}}
                <h1 class="tagline">
                    Your wishlist.<br>
                    <span class="tagline-highlight">Good done quietly.</span>
                </h1>

                {{-- Subtitle --}}
                <p class="subtitle">
                    Share it with friends and family. When they buy, we donate to charity. Simple as that.
                </p>

                {{-- Badges --}}
                <div class="badges">
                    <span class="badge badge-coral">&#127873; No duplicate gifts</span>
                    <span class="badge badge-teal"><x-heart-icon class="heart-svg-inline" /> 100% to charity</span>
                    <span class="badge badge-sunny">&#10003; Free to use</span>
                </div>
            </div>

            {{-- Visual card --}}
            <div class="visual-card">
                <div class="visual-card-header">
                    <span class="visual-card-icon">&#127873;</span>
                    <div>
                        <div class="visual-card-title">Birthday wishlist</div>
                        <div class="visual-card-subtitle">3 gift ideas</div>
                    </div>
                </div>
                <div class="visual-item">
                    <div class="visual-item-emoji visual-item-emoji-1">&#127911;</div>
                    <div class="visual-item-text">
                        <div class="visual-item-name">Headphones</div>
                        <div class="visual-item-price">€ 79</div>
                    </div>
                    <span class="visual-item-badge item-badge-available">Available</span>
                </div>
                <div class="visual-item">
                    <div class="visual-item-emoji visual-item-emoji-2">&#129507;</div>
                    <div class="visual-item-text">
                        <div class="visual-item-name">Cozy Blanket</div>
                        <div class="visual-item-price">€ 45</div>
                    </div>
                    <span class="visual-item-badge item-badge-claimed">Claimed</span>
                </div>
                <div class="visual-item">
                    <div class="visual-item-emoji visual-item-emoji-3">&#128218;</div>
                    <div class="visual-item-text">
                        <div class="visual-item-name">Book Set</div>
                        <div class="visual-item-price">€ 32</div>
                    </div>
                    <span class="visual-item-badge item-badge-available">Available</span>
                </div>
            </div>

        {{-- Domain --}}
        <div class="domain">givetwice.app</div>
    </div>
</x-dev.layouts.base>
