<x-dev.layouts.base>
    <x-slot:title>Logo with Text</x-slot:title>
    <x-slot:dimensions>800 × 400</x-slot:dimensions>

    <x-slot:instructions>
        Screenshot the canvas. Save as <code>public/images/logo-text.png</code>
    </x-slot:instructions>

    <x-slot:styles>
        .canvas {
            width: 800px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-lockup {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .heart-svg {
            width: 240px;
            height: 240px;
            flex-shrink: 0;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            font-weight: 700;
            font-size: 120px;
            line-height: 0.9;
            letter-spacing: -0.03em;
        }
    </x-slot:styles>

    <div class="canvas bg-gradient-warm">
        <div class="logo-lockup">
            <x-heart-icon class="heart-svg" />
            <div class="logo-text">
                <span class="text-gray-900">Give</span>
                <span class="text-coral-500">Twice</span>
            </div>
        </div>
    </div>
</x-dev.layouts.base>
