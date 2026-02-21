<div
    x-data="installBanner()"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    x-cloak
    class="fixed bottom-6 left-4 right-4 z-40 max-w-lg mx-auto safe-area-bottom"
    role="complementary"
    aria-label="{{ __('Install app') }}"
>
    <div class="bg-white border border-cream-300 rounded-2xl shadow-lg p-4 flex items-center gap-3">
        <div class="flex-shrink-0 w-10 h-10 bg-coral-100 rounded-xl flex items-center justify-center">
            <span class="text-lg">🎁</span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900">{{ __('Install GiveTwice') }}</p>
            <p class="text-xs text-gray-500 mt-0.5" x-text="instructionText"></p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button
                x-show="canInstallNative"
                @click="installNative()"
                type="button"
                class="btn-primary-sm"
            >
                {{ __('Install') }}
            </button>
            <button
                @click="dismiss()"
                type="button"
                class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-cream-100 transition-colors"
                aria-label="{{ __('Dismiss') }}"
            >
                <x-icons.close class="w-4 h-4" />
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('installBanner', () => ({
        visible: false,
        canInstallNative: false,
        deferredPrompt: null,
        instructionText: '',

        init() {
            if (window.isStandalonePwa || this.isDismissed()) return;

            if (this.incrementAndGetPageVisits() < 2) return;

            if (this.isIosSafari()) {
                this.instructionText = @js(__('Tap Share, then "Add to Home Screen"'));
                this.visible = true;
            }

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                this.canInstallNative = true;
                this.instructionText = @js(__('Add to your home screen for quick access'));
                this.visible = true;
            });

            window.addEventListener('appinstalled', () => {
                this.visible = false;
                this.deferredPrompt = null;
            });
        },

        async installNative() {
            if (!this.deferredPrompt) return;
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            this.deferredPrompt = null;
            this.canInstallNative = false;
            if (outcome === 'accepted') {
                this.visible = false;
            }
        },

        dismiss() {
            this.visible = false;
            try {
                localStorage.setItem('givetwice_install_dismissed', Date.now().toString());
            } catch {}
        },

        isDismissed() {
            try {
                const dismissed = localStorage.getItem('givetwice_install_dismissed');
                if (!dismissed) return false;
                const sevenDays = 7 * 24 * 60 * 60 * 1000;
                return (Date.now() - parseInt(dismissed, 10)) < sevenDays;
            } catch {
                return false;
            }
        },

        incrementAndGetPageVisits() {
            try {
                const count = parseInt(localStorage.getItem('givetwice_page_visits') || '0', 10) + 1;
                localStorage.setItem('givetwice_page_visits', count.toString());
                return count;
            } catch {
                return 0;
            }
        },

        isIosSafari() {
            const ua = window.navigator.userAgent;
            const isIos = /iPad|iPhone|iPod/.test(ua) || (navigator.maxTouchPoints > 1 && /Macintosh/.test(ua));
            const isSafari = /Safari/.test(ua) && !/CriOS|FxiOS|OPiOS|EdgiOS/.test(ua);
            return isIos && isSafari && !window.navigator.standalone;
        }
    }));
});
</script>
@endpush
