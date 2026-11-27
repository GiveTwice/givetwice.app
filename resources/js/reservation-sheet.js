export default () => ({
    busy: false, error: '', touchY: 0, changed: false, opener: null,
    async open(url) {
        if (this.busy) return;
        if (this.$refs.dialog && !this.$refs.dialog.open) {
            this.opener = document.activeElement;
            const header = document.querySelector('body > header');
            const top = Math.max(72, header?.getBoundingClientRect().bottom || 0);
            this.$refs.dialog.style.setProperty('--sheet-top', `${top}px`);
            this.$refs.dialog.showModal();
            document.body.style.overflow = 'hidden';
        }
        await this.request(url);
    },
    async send(form) {
        if (this.busy) return;
        // A submit always shows the loading state for at least half a second, so the next screen does not flash in.
        await this.request(form.action, { method: 'POST', body: new FormData(form) }, 500);
    },
    async request(url, options = {}, minimumMs = 0) {
        this.busy = true;
        this.error = '';
        try {
            const [response] = await Promise.all([
                fetch(url, { ...options, headers: { 'X-Reservation-Sheet': '1', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } }),
                new Promise((resolve) => setTimeout(resolve, minimumMs)),
            ]);
            if (!response.ok) {
                const data = await response.json();
                this.error = Object.values(data.errors || {}).flat().join(' ') || data.message;
                // Cloudflare accepts each token once, so a failed send needs a fresh challenge before the retry.
                this.$refs.content.querySelectorAll('[data-turnstile]').forEach((el) => window.turnstile?.reset(el.dataset.turnstile));
                return;
            }
            this.$refs.content.innerHTML = await response.text();
            if (options.method) this.changed = true;
            this.$refs.dialog?.setAttribute('aria-label', this.$refs.content.querySelector('h2')?.textContent || 'GiveTwice');
        } catch {
            this.error = document.documentElement.lang === 'nl' ? 'Geen verbinding. Probeer opnieuw.' : document.documentElement.lang === 'fr' ? 'Pas de connexion. Réessaie.' : 'No connection. Try again.';
        } finally { this.busy = false; }
    },
    close() { this.$refs.dialog.close(); },
    closed() {
        document.body.style.overflow = '';
        this.opener?.focus();
        if (this.changed) window.location.reload();
    },
});
