// Saves the order of the gifts in one grid, after the owner drags a gift or moves it with the arrow keys.
export default (config) => ({
    status: '',
    failed: false,
    timer: null,
    // Each save waits for the one before it, so the server always ends up with the last order.
    queue: Promise.resolve(),

    order() {
        return [...this.$refs.grid.querySelectorAll(':scope > [data-sort-id]')].map((item) => Number(item.dataset.sortId));
    },

    move(handle, step) {
        const item = handle.closest('[data-sort-id]');
        const neighbour = step < 0 ? item.previousElementSibling : item.nextElementSibling;
        if (!neighbour?.matches('[data-sort-id]')) return;

        if (step < 0) {
            neighbour.before(item);
        } else {
            neighbour.after(item);
        }
        handle.focus();
        this.save();
    },

    save() {
        const gifts = this.order();
        this.queue = this.queue.then(() => this.send(gifts));
    },

    async send(gifts) {
        try {
            const response = await fetch(config.url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({ gifts }),
            });
            if (!response.ok) throw new Error(`Saving the order failed with ${response.status}`);
            this.notify(config.saved, false);
        } catch {
            this.notify(config.failed, true);
        }
    },

    notify(message, failed) {
        this.failed = failed;
        this.status = message;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => { this.status = ''; }, failed ? 8000 : 2500);
    },
});
