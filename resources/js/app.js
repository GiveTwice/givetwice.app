import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Public list page component for real-time gift updates
Alpine.data('publicList', (config) => ({
    availableCount: config.availableCount,
    claimedCount: config.claimedCount,

    init() {
        if (window.Echo) {
            window.Echo.channel('list.' + config.slug)
                .listen('.gift.added', (e) => this.addGiftCard(e.gift))
                .listen('.gift.fetch.completed', (e) => this.updateGiftCard(e.gift))
                .listen('.gift.claimed', (e) => this.markGiftAsClaimed(e.gift.id));
        }
    },

    addGiftCard(gift) {
        if (document.querySelector(`[data-gift-id='${gift.id}']`)) return;
        const grid = document.querySelector('[data-gift-grid]');
        if (!grid) return;
        const emptyState = document.querySelector('[data-empty-state]');
        if (emptyState) {
            emptyState.remove();
            grid.classList.remove('hidden');
        }
        const self = this;
        fetch(`/${config.locale}/view/${config.slug}/gifts/${gift.id}/card`)
            .then(response => response.ok ? response.text() : Promise.reject())
            .then(html => {
                // HTML is from our own server endpoint, safe to parse
                const temp = document.createElement('div');
                temp.innerHTML = html; // eslint-disable-line no-unsanitized/property
                const card = temp.firstElementChild;
                if (!card) return;
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px)';
                grid.insertBefore(card, grid.firstChild);
                requestAnimationFrame(() => {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                });
                self.availableCount++;
            })
            .catch(() => {});
    },

    updateGiftCard(gift) {
        const card = document.querySelector(`[data-gift-id='${gift.id}']`);
        if (!card) return;
        const imgContainer = card.querySelector('[data-gift-image]');
        if (imgContainer && gift.image_url_card) {
            const placeholder = imgContainer.querySelector('[data-gift-placeholder]');
            if (placeholder) placeholder.remove();
            const existingImg = imgContainer.querySelector('img');
            if (existingImg) existingImg.remove();
            const img = document.createElement('img');
            img.src = gift.image_url_card;
            img.alt = gift.title || '';
            img.className = 'w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500';
            img.loading = 'lazy';
            imgContainer.insertBefore(img, imgContainer.firstChild);
        }
        const badge = card.querySelector('[data-gift-badge]');
        if (badge) badge.remove();
        const titleEl = card.querySelector('[data-gift-title]');
        if (titleEl) {
            titleEl.textContent = gift.title || config.translations.untitledGift;
            titleEl.title = gift.title || '';
        }
        const priceContainer = card.querySelector('[data-gift-price]');
        if (priceContainer && gift.price_formatted) {
            priceContainer.replaceChildren();
            const priceSpan = document.createElement('span');
            priceSpan.className = 'text-base font-bold text-coral-600';
            priceSpan.textContent = gift.price_formatted;
            priceContainer.appendChild(priceSpan);
        }
        this.updateGiftModal(gift);
    },

    updateGiftModal(gift) {
        const modalWrapper = document.querySelector(`[x-on\\:open-gift-modal-${gift.id}\\.window]`);
        if (!modalWrapper) return;
        const modalImg = modalWrapper.querySelector('img');
        if (modalImg && gift.image_url_large) {
            modalImg.src = gift.image_url_large;
            modalImg.alt = gift.title || '';
        }
        const modalTitle = modalWrapper.querySelector('h2');
        if (modalTitle) {
            modalTitle.textContent = gift.title || config.translations.untitledGift;
        }
    },

    markGiftAsClaimed(giftId) {
        const card = document.querySelector(`[data-gift-id='${giftId}']`);
        if (!card) return;
        this.availableCount = Math.max(0, this.availableCount - 1);
        this.claimedCount++;
        const imgContainer = card.querySelector('[data-gift-image]');
        if (imgContainer && !imgContainer.querySelector('.claimed-badge')) {
            const badgeWrapper = document.createElement('div');
            badgeWrapper.className = 'absolute top-3 right-3 claimed-badge';
            const badgeSpan = document.createElement('span');
            badgeSpan.className = 'inline-flex items-center gap-1 px-2.5 py-1 bg-sunny-100/95 backdrop-blur-sm text-sunny-700 text-xs font-semibold rounded-full shadow-sm';
            badgeSpan.textContent = config.translations.claimed;
            badgeWrapper.appendChild(badgeSpan);
            imgContainer.appendChild(badgeWrapper);
        }
        const claimForm = card.querySelector('form[action*="/claim"]');
        const claimLink = card.querySelector('a[href*="/claim"]');
        if (claimForm) {
            const disabledBtn = document.createElement('button');
            disabledBtn.type = 'button';
            disabledBtn.disabled = true;
            disabledBtn.className = 'w-full text-xs bg-cream-200 text-cream-500 px-3 py-2 rounded-lg cursor-not-allowed';
            disabledBtn.textContent = config.translations.alreadyClaimed;
            claimForm.replaceWith(disabledBtn);
        } else if (claimLink) {
            const disabledBtn = document.createElement('button');
            disabledBtn.type = 'button';
            disabledBtn.disabled = true;
            disabledBtn.className = 'w-full text-xs bg-cream-200 text-cream-500 px-3 py-2 rounded-lg cursor-not-allowed';
            disabledBtn.textContent = config.translations.alreadyClaimed;
            claimLink.replaceWith(disabledBtn);
        }
    }
}));

Alpine.start();
