import './bootstrap';
import Alpine from 'alpinejs';
import reservationSheet from './reservation-sheet';
Alpine.data('reservationSheet', reservationSheet);

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
    });
}

window.isStandalonePwa = window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

// Standalone PWA: intercept link clicks to fix navigation quirks
if (window.isStandalonePwa) {
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
        if (link.hasAttribute('download')) return;

        let url;
        try {
            url = new URL(href, window.location.origin);
        } catch {
            return;
        }

        const isExternal = url.origin !== window.location.origin;
        const isOAuth = /^\/[a-z]{2}\/auth\/(google|facebook|apple)$/.test(url.pathname);

        if (isExternal || isOAuth) {
            // Open external links and OAuth flows in Safari to prevent
            // replacing the PWA or polluting the history stack
            e.preventDefault();
            window.open(link.href, '_blank');
        } else if (link.target === '_blank') {
            // Internal links with target="_blank" should navigate within
            // the PWA instead of opening a new Safari tab
            e.preventDefault();
            window.location.href = link.href;
        }
    });
}

window.Alpine = Alpine;

// Reload the current filter so sections, counts and gift details stay in sync.
Alpine.data('publicList', (config) => ({
    init() {
        if (/^#gift-\d+$/.test(window.location.hash)) {
            this.$nextTick(() => document.querySelector(window.location.hash + ' > a')?.click());
        }
        if (!window.Echo) return;
        const refresh = () => { if (!document.querySelector('.reservation-dialog[open]')) window.location.reload(); };
        window.Echo.channel('list.' + config.slug)
            .listen('.gift.added', refresh)
            .listen('.gift.fetch.completed', refresh);
        if (config.showReservations) {
            window.Echo.channel('list.' + config.slug + '.reservations')
                .listen('.gift.claimed', refresh);
        }
    }
}));

// Follow button component for public list pages
Alpine.data('followButton', (config) => ({
    following: config.following,
    loading: false,

    async toggle() {
        this.loading = true;

        try {
            const url = `/${config.locale}/friends/follow/${config.slug}`;
            const response = await fetch(url, {
                method: this.following ? 'DELETE' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.following = data.following;
            }
        } catch (error) {
            console.error('Error toggling follow:', error);
        } finally {
            this.loading = false;
        }
    }
}));

Alpine.start();
