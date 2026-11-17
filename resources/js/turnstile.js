const script = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';

// Loads Cloudflare's script on first use, so pages without a protected form never fetch it.
export default (sitekey, language) => ({
    widget: null,
    init() {
        if (!document.querySelector(`script[src="${script}"]`)) {
            document.head.append(Object.assign(document.createElement('script'), { src: script, async: true }));
        }
        const draw = () => {
            if (!this.$el.isConnected) return;
            if (!window.turnstile) return setTimeout(draw, 100);
            this.widget = window.turnstile.render(this.$el, { sitekey, language, theme: 'light', size: 'flexible' });
            this.$el.dataset.turnstile = this.widget;
        };
        draw();
    },
    destroy() {
        if (this.widget) window.turnstile.remove(this.widget);
    },
});
