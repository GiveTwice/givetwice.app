<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OG Image Preview - GiveTwice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .instructions {
            margin-bottom: 24px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .instructions code {
            background: #e5e5e5;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
        }

        /* Screenshot guide border */
        .og-wrapper {
            position: relative;
            background: white;
            padding: 2px;
            border: 2px dashed #999;
            border-radius: 4px;
        }

        .og-wrapper::before {
            content: '1200 × 630';
            position: absolute;
            top: -28px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: #666;
            font-family: monospace;
            background: white;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* The actual OG image canvas */
        .og-image {
            width: 1200px;
            height: 630px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(
                135deg,
                oklch(0.99 0.01 80) 0%,
                oklch(0.97 0.03 70) 40%,
                oklch(0.96 0.04 50) 100%
            );
        }

        /* Decorative elements */
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

        /* Gift icons floating */
        .gift-icon {
            position: absolute;
            font-size: 48px;
            opacity: 0.15;
        }

        .gift-1 { top: 80px; right: 120px; transform: rotate(-15deg); }
        .gift-2 { bottom: 100px; right: 200px; transform: rotate(10deg); font-size: 36px; }
        .gift-3 { top: 180px; right: 280px; transform: rotate(5deg); font-size: 32px; opacity: 0.1; }

        /* Main content area */
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

        .logo-heart {
            font-size: 72px;
            color: oklch(0.57 0.19 25);
            line-height: 1;
        }

        .logo-text {
            font-size: 64px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .logo-give {
            color: #111827;
        }

        .logo-twice {
            color: oklch(0.57 0.19 25);
        }

        /* Tagline */
        .tagline {
            font-size: 42px;
            font-weight: 600;
            color: #374151;
            line-height: 1.3;
            max-width: 700px;
            margin-bottom: 32px;
        }

        .tagline-highlight {
            color: oklch(0.57 0.19 25);
        }

        /* Subtitle */
        .subtitle {
            font-size: 24px;
            color: #6b7280;
            max-width: 600px;
            line-height: 1.5;
        }

        /* Badge row */
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
            background: oklch(0.94 0.04 25);
            color: oklch(0.50 0.16 25);
        }

        .badge-teal {
            background: oklch(0.94 0.05 175);
            color: oklch(0.48 0.10 175);
        }

        .badge-sunny {
            background: oklch(0.97 0.05 95);
            color: oklch(0.58 0.14 70);
        }

        /* Domain hint */
        .domain {
            position: absolute;
            bottom: 40px;
            right: 60px;
            font-size: 22px;
            font-weight: 600;
            color: #9ca3af;
            letter-spacing: 0.02em;
        }

        /* Right side visual element */
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
            color: #111827;
        }

        .visual-card-subtitle {
            font-size: 13px;
            color: #9ca3af;
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
            color: #374151;
        }

        .visual-item-price {
            font-size: 13px;
            font-weight: 700;
            color: oklch(0.57 0.19 25);
        }

        .visual-item-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 100px;
        }

        .badge-available {
            background: oklch(0.94 0.05 175);
            color: oklch(0.48 0.10 175);
        }

        .badge-claimed {
            background: oklch(0.97 0.05 95);
            color: oklch(0.58 0.14 70);
        }
    </style>
</head>
<body>
    <div class="instructions">
        Screenshot the area inside the dashed border. Save as <code>public/images/og-image.png</code>
    </div>

    <div class="og-wrapper">
        <div class="og-image">
            <!-- Decorative circles -->
            <div class="deco-circle deco-circle-1"></div>
            <div class="deco-circle deco-circle-2"></div>
            <div class="deco-circle deco-circle-3"></div>

            <!-- Floating gift icons -->
            <span class="gift-icon gift-1">&#127873;</span>
            <span class="gift-icon gift-2">&#127873;</span>
            <span class="gift-icon gift-3">&#127873;</span>

            <!-- Main content -->
            <div class="content">
                <!-- Logo -->
                <div class="logo">
                    <span class="logo-heart">&#10084;</span>
                    <span class="logo-text">
                        <span class="logo-give">Give</span><span class="logo-twice">Twice</span>
                    </span>
                </div>

                <!-- Tagline -->
                <h1 class="tagline">
                    Every gift gives <span class="tagline-highlight">twice</span>
                </h1>

                <!-- Subtitle -->
                <p class="subtitle">
                    Create wishlists your loved ones will love. When they buy, charity wins too.
                </p>

                <!-- Badges -->
                <div class="badges">
                    <span class="badge badge-coral">&#127873; Perfect gifts</span>
                    <span class="badge badge-teal">&#10084; 100% to charity</span>
                    <span class="badge badge-sunny">&#10003; Always free</span>
                </div>
            </div>

            <!-- Visual card element -->
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
                    <span class="visual-item-badge badge-available">Available</span>
                </div>
                <div class="visual-item">
                    <div class="visual-item-emoji visual-item-emoji-2">&#129507;</div>
                    <div class="visual-item-text">
                        <div class="visual-item-name">Cozy Blanket</div>
                        <div class="visual-item-price">€ 45</div>
                    </div>
                    <span class="visual-item-badge badge-claimed">Claimed</span>
                </div>
                <div class="visual-item">
                    <div class="visual-item-emoji visual-item-emoji-3">&#128218;</div>
                    <div class="visual-item-text">
                        <div class="visual-item-name">Book Set</div>
                        <div class="visual-item-price">€ 32</div>
                    </div>
                    <span class="visual-item-badge badge-available">Available</span>
                </div>
            </div>

            <!-- Domain -->
            <div class="domain">givetwice.app</div>
        </div>
    </div>
</body>
</html>
