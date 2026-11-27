const script = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';

// Loads Cloudflare's script on first use, so pages without a protected form never fetch it.
// The widget stays hidden while Cloudflare checks the visitor in the background. It only appears when Cloudflare wants a click.
export default (sitekey, language) => ({
    widget: null,
    visible: false,
    failed: false,
    waiting: [],
    init() {
        if (!document.querySelector(`script[src="${script}"]`)) {
            document.head.append(Object.assign(document.createElement('script'), { src: script, async: true }));
        }
        this.$el.closest('form')?.addEventListener('submit', (event) => this.holdUntilChecked(event), true);
        const draw = () => {
            if (!this.$el.isConnected) return;
            if (!window.turnstile) return setTimeout(draw, 100);
            this.widget = window.turnstile.render(this.$el, {
                sitekey,
                language,
                theme: 'light',
                size: 'flexible',
                appearance: 'interaction-only',
                callback: () => this.release(),
                'error-callback': () => { this.failed = true; this.release(); },
                'before-interactive-callback': () => { this.visible = true; },
                'after-interactive-callback': () => { this.visible = false; },
            });
            this.$el.dataset.turnstile = this.widget;
        };
        draw();
    },
    // A click on submit can come before the background check has finished. The form then waits for the token instead of sending an empty one.
    holdUntilChecked(event) {
        if (this.failed || this.$el.querySelector('[name="cf-turnstile-response"]')?.value) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        if (this.waiting.length) return;
        this.waiting.push(() => event.target.requestSubmit(event.submitter));
    },
    release() {
        this.waiting.splice(0).forEach((submit) => submit());
    },
    destroy() {
        if (this.widget) window.turnstile.remove(this.widget);
    },
});
