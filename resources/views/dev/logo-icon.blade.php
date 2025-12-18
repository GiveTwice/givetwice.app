<x-dev.layouts.base>
    <x-slot:title>Logo Icon</x-slot:title>
    <x-slot:dimensions>512 × 512</x-slot:dimensions>

    <x-slot:instructions>
        Screenshot the canvas. Save as <code>public/images/logo-icon.png</code>
    </x-slot:instructions>

    <x-slot:styles>
        .canvas {
            width: 512px;
            height: 512px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 64px;
        }

        .canvas .heart-svg {
            width: 100%;
            height: 100%;
        }
    </x-slot:styles>

    <div class="canvas bg-gradient-warm">
        <x-heart-icon class="heart-svg" />
    </div>
</x-dev.layouts.base>
