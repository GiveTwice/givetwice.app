<x-dev.layouts.base>
    <x-slot:title>OG Image Preview</x-slot:title>

    <x-slot:dimensions>1200 × 630</x-slot:dimensions>

    <x-slot:instructions>
        Screenshot the canvas. Save as <code>public/images/og-image.png</code>
    </x-slot:instructions>

    <x-slot:styles>
        /* OG Image canvas - fixed dimensions for screenshot */
        .canvas {
            width: 1200px;
            height: 630px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles - use Tailwind color tokens */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.5;
        }

        .deco-circle-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, theme(colors.sunny.200 / 60%) 0%, transparent 70%);
            top: -150px;
            right: -100px;
        }

        .deco-circle-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, theme(colors.teal.100 / 40%) 0%, transparent 70%);
            bottom: -120px;
            left: -80px;
        }

        .deco-circle-3 {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, theme(colors.coral.100 / 50%) 0%, transparent 70%);
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

        .content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 100px;
        }

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

        .tagline {
            font-size: 42px;
            font-weight: 600;
            line-height: 1.3;
            max-width: 700px;
            margin-bottom: 32px;
        }

        .subtitle {
            font-size: 24px;
            max-width: 600px;
            line-height: 1.5;
        }

        .badges {
            display: flex;
            gap: 16px;
            margin-top: 48px;
        }

        /* Badge base - OG-specific sizing (larger than app badges) */
        .og-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 100px;
            font-size: 18px;
            font-weight: 600;
        }

        .og-badge .heart-svg-inline {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            margin-right: 4px;
        }

        .domain {
            position: absolute;
            bottom: 40px;
            right: 60px;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

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
                0 0 0 1px theme(colors.cream.300 / 50%);
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

        .visual-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid theme(colors.cream.200);
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

        .visual-item-emoji-1 { background: linear-gradient(135deg, theme(colors.blue.100), theme(colors.blue.200)); }
        .visual-item-emoji-2 { background: linear-gradient(135deg, theme(colors.amber.100), theme(colors.amber.200)); }
        .visual-item-emoji-3 { background: linear-gradient(135deg, theme(colors.emerald.100), theme(colors.emerald.200)); }

        .visual-item-text {
            flex: 1;
        }

        /* Item badge - OG-specific sizing */
        .og-item-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 100px;
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
                <h1 class="tagline text-gray-700">
                    Your wishlist.<br>
                    <span class="text-coral-500">Good done quietly.</span>
                </h1>

                {{-- Subtitle --}}
                <p class="subtitle text-gray-500">
                    Share it with friends and family. When they buy, we donate to charity. Simple as that.
                </p>

                {{-- Badges - using Tailwind color classes --}}
                <div class="badges">
                    <span class="og-badge bg-coral-100 text-coral-700">&#127873; No duplicate gifts</span>
                    <span class="og-badge bg-teal-100 text-teal-700"><x-heart-icon class="heart-svg-inline" /> 100% to charity</span>
                    <span class="og-badge bg-sunny-100 text-sunny-700">&#10003; Free to use</span>
                </div>
            </div>

            {{-- Visual card --}}
            <div class="visual-card">
                <div class="visual-card-header">
                    <span class="visual-card-icon">&#127873;</span>
                    <div>
                        <div class="text-lg font-bold text-gray-900">Birthday wishlist</div>
                        <div class="text-sm text-gray-400">3 gift ideas</div>
                    </div>
                </div>
                <div class="visual-item">
                    <div class="visual-item-emoji visual-item-emoji-1">&#127911;</div>
                    <div class="visual-item-text">
                        <div class="text-sm font-semibold text-gray-700">Headphones</div>
                        <div class="text-sm font-bold text-coral-500">€ 79</div>
                    </div>
                    <span class="og-item-badge bg-teal-100 text-teal-700">Available</span>
                </div>
                <div class="visual-item">
                    <div class="visual-item-emoji visual-item-emoji-2">&#129507;</div>
                    <div class="visual-item-text">
                        <div class="text-sm font-semibold text-gray-700">Cozy Blanket</div>
                        <div class="text-sm font-bold text-coral-500">€ 45</div>
                    </div>
                    <span class="og-item-badge bg-sunny-100 text-sunny-700">Claimed</span>
                </div>
                <div class="visual-item">
                    <div class="visual-item-emoji visual-item-emoji-3">&#128218;</div>
                    <div class="visual-item-text">
                        <div class="text-sm font-semibold text-gray-700">Book Set</div>
                        <div class="text-sm font-bold text-coral-500">€ 32</div>
                    </div>
                    <span class="og-item-badge bg-teal-100 text-teal-700">Available</span>
                </div>
            </div>

        {{-- Domain --}}
        <div class="domain text-gray-400">givetwice.app</div>
    </div>
</x-dev.layouts.base>
