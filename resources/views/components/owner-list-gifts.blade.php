@props(['list'])

<div
    x-data="{
        init() {
            if (window.Echo) {
                window.Echo.private('user.{{ auth()->id() }}')
                    .listen('.gift.fetch.completed', (e) => {
                        this.updateGiftCard(e.gift);
                    });
            }
        },
        updateGiftCard(gift) {
            const card = document.querySelector(`[data-gift-id='${gift.id}']`);
            if (!card) return;

            const imgContainer = card.querySelector('[data-gift-image]');
            if (imgContainer && gift.image_url_card) {
                imgContainer.classList.remove('h-20');
                imgContainer.classList.add('aspect-square');
                const placeholder = imgContainer.querySelector('[data-gift-placeholder]');
                if (placeholder) {
                    placeholder.remove();
                }

                const existingImg = imgContainer.querySelector('img');
                if (existingImg) {
                    existingImg.remove();
                }

                const img = document.createElement('img');
                img.src = gift.image_url_card;
                img.alt = gift.title || '';
                img.className = 'w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500';
                img.loading = 'lazy';
                imgContainer.insertBefore(img, imgContainer.firstChild);
            }

            const titleEl = card.querySelector('[data-gift-title]');
            if (titleEl) {
                titleEl.textContent = gift.title || @js(__('Untitled gift'));
                titleEl.title = gift.title || '';
            }

            const priceEl = card.querySelector('[data-gift-price]');
            if (priceEl && gift.price_formatted) {
                priceEl.textContent = '';
                const priceSpan = document.createElement('span');
                priceSpan.className = 'text-lg font-bold text-coral-600';
                priceSpan.textContent = gift.price_formatted;
                priceEl.appendChild(priceSpan);
            }

            const badgeEl = card.querySelector('[data-gift-badge]');
            if (badgeEl && gift.fetch_status === 'completed') {
                badgeEl.remove();
            }
        }
    }"
>
    @unless($list->spoiler_mode)
        <p class="mb-4 text-sm text-gray-500">{{ __('Surprise protected. You cannot see what is reserved.') }}</p>
    @endunless

    @if($list->gifts->isEmpty())
        <div class="py-12 text-center">
            <div class="mx-auto max-w-md">
                <h3 class="mb-2 text-xl font-semibold text-gray-900">{{ __('No gifts yet') }}</h3>
                <p class="mb-6 text-gray-500">{{ __('Paste a shop link, or just type your wish.') }}</p>
                <a href="{{ route('gifts.create', ['locale' => app()->getLocale(), 'list' => $list->id]) }}" class="btn-primary">
                    <x-icons.plus class="w-5 h-5" />
                    {{ __('Add Your First Gift') }}
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($list->gifts as $gift)
                <div>
                    <x-gift-card :gift="$gift" :list="$list" :editable="true" :show-reservations="$list->spoiler_mode" />
                    <x-gift-order-controls :list="$list" :gift="$gift" :first="$loop->first" :last="$loop->last" />
                </div>
            @endforeach
        </div>
    @endif
</div>
