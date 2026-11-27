@php($parameters = ['locale' => app()->getLocale(), 'gift' => $gift, 'list' => $list?->id])
<div class="flex flex-col gap-3 text-sm wrap-anywhere" x-data="{ editing: false, email: @js($email) }" data-reservation-state="{{ $late ? 'late' : ($claim ? 'reserved' : 'free') }}">
    @if($notice)<p role="status" class="rounded-lg bg-teal-50 p-3 text-teal-800">{{ $notice }}</p>@endif
    @if($late)
        <x-section-heading>{{ __('Just too late') }}</x-section-heading>
        <p class="text-gray-600">{{ __('Someone just reserved :gift. Here are a few other ideas.', ['gift' => $gift->title]) }}</p>
        <h3 class="font-semibold">{{ __('Still free, around the same price') }}</h3>
        @foreach($alternatives as $alternative)
            <a class="flex items-center gap-3 rounded-lg border border-cream-200 p-2" href="{{ route('claim.anonymous.form', ['locale' => app()->getLocale(), 'gift' => $alternative, 'list' => $list->id]) }}" x-on:click.prevent="open($el.href)">
                @if($alternative->hasImage())<img src="{{ $alternative->getImageUrl('thumb') }}" alt="" class="h-12 w-12 rounded-lg object-contain">@endif
                <span>{{ $alternative->title }}<span class="block text-sm text-gray-500">{{ $alternative->formatPrice() }} {{ $alternative->siteName() }}</span></span>
            </a>
        @endforeach
    @elseif($claim)
        <div class="info-box-success flex items-start gap-3">
            <x-icons.check-circle-filled class="mt-0.5 h-6 w-6 shrink-0 text-teal-600" />
            <div class="min-w-0">
                <h2 class="text-lg font-bold leading-7 text-gray-900">{{ __('Reserved for you') }}</h2>
                <p class="font-medium text-gray-900">{{ $gift->title }}</p>
                <p class="mt-1 text-teal-800">{{ $gift->allowsMultipleClaims() ? '' : __('Nobody else can reserve it now.') }} {{ $gift->url ? __('You buy it yourself in the shop.') : __('You buy it yourself, wherever you like.') }}</p>
            </div>
        </div>
        <div x-show="!editing" class="flex flex-col gap-3">
            @if($gift->url)<a href="{{ $gift->url }}" target="_blank" rel="noopener noreferrer" class="btn-primary w-full">{{ __('Go to the shop') }} ↗</a>@endif
            <p class="text-gray-600">{!! __('Receipt goes to :email.', ['email' => '<strong class="text-gray-900">'.e($email).'</strong>']) !!} <button type="button" x-on:click="editing = true; $nextTick(() => $refs.email.focus())" class="font-medium whitespace-nowrap text-coral-600 hover:text-coral-700">{{ __('Change') }}</button></p>
            @if($suggestion)<button type="button" class="text-left text-coral-600" x-on:click="editing = true; email = @js($suggestion); $nextTick(() => $refs.email.focus())">{{ __('Did you mean :email?', ['email' => $suggestion]) }}</button>@endif
        </div>
        <form x-show="editing" x-cloak method="POST" action="{{ route('claim.email', $parameters) }}" x-on:submit.prevent="$dispatch('reservation-submit', {form: $el})" class="flex flex-col gap-2">
            @csrf @method('PATCH')
            <label for="reservation-email" class="form-label mb-0">{{ __('Change email') }}</label>
            <input id="reservation-email" x-ref="email" x-model="email" type="email" name="email" required maxlength="255" autocomplete="email" placeholder="{{ __('Your email address') }}" class="form-input w-full min-w-0">
            <button class="btn-primary w-full" :disabled="busy">{{ __('Save and send again') }}</button>
            <button type="button" x-on:click="editing = false" class="btn-cancel">{{ __('Cancel') }}</button>
        </form>
        @auth
            <p class="text-sm text-gray-600">{{ __('Reserving follows this list automatically. You can turn updates off in Followed lists.') }} <a class="inline-flex min-h-11 items-center text-coral-700" href="{{ route('friends.index', ['locale' => app()->getLocale()]) }}">{{ __('Followed lists') }}</a></p>
        @endauth
        <div x-show="!editing" class="flex flex-wrap items-center justify-between gap-x-4">
            @if($list)<a x-show="! $refs.dialog" href="{{ $list->getPublicUrl() }}" class="btn-cancel whitespace-nowrap px-0">{{ __('Back to wishlist') }}</a>@endif
            <form method="POST" action="{{ route('claim.cancel', $parameters) }}" x-on:submit.prevent="$dispatch('reservation-submit', {form: $el})">@csrf @method('DELETE')<button class="btn-cancel whitespace-nowrap px-0 font-medium text-gray-500">{{ __('Undo reservation') }}</button></form>
        </div>
    @else
        @if($list)<p class="text-gray-500">{{ __(':list by :name', ['list' => $list->name, 'name' => $list->creator?->name ?? $gift->user->name]) }}</p>@endif
        @if($gift->hasImage())
            <img src="{{ $gift->getImageUrl('card') }}" alt="" class="h-32 w-full rounded-xl bg-cream-50 object-contain">
        @else
            <div class="flex h-20 items-center justify-center rounded-xl bg-cream-100 text-gray-500">{{ __('No image') }}</div>
        @endif
        <h3 class="text-base font-semibold text-gray-900">{{ $gift->title }}</h3>
        <div class="flex flex-wrap items-center justify-between gap-2 text-gray-500">
            <span>{{ $gift->formatPrice() }}{{ $gift->hasPrice() && $gift->siteName() ? ' · ' : '' }}{{ $gift->siteName() }}</span>
            @if($gift->url)<a class="btn-link" href="{{ $gift->url }}" target="_blank" rel="noopener noreferrer">{{ __('View in shop') }} ↗</a>@endif
        </div>
        @unless($gift->url)<p class="text-gray-500">{{ __('Buy it wherever you like.') }}</p>@endunless
        @if($gift->owner_note)
            <div class="rounded-lg bg-cream-100 p-3"><p class="text-sm font-semibold text-gray-500">{{ __('Note from :name', ['name' => $list?->creator?->name ?? $gift->user->name]) }}</p>{{ $gift->owner_note }}</div>
        @endif
        <form method="POST" action="{{ route('claim.anonymous.store', $parameters) }}" x-on:submit.prevent="$dispatch('reservation-submit', {form: $el})" class="flex flex-col gap-2">
            @csrf
            @guest
                <label for="reservation-email" class="sr-only">{{ __('Email') }}</label>
                <input id="reservation-email" x-ref="email" x-model="email" type="email" name="email" required maxlength="255" autocomplete="email" placeholder="{{ __('Your email address') }}" class="form-input w-full min-w-0">
                @if($suggestion)<button type="button" x-on:click="email = @js($suggestion)" class="text-left text-coral-600">{{ $suggestion }}</button>@endif
                <x-turnstile />
            @endguest
            <button class="btn-primary w-full" :disabled="busy">{{ __('Reserve this gift') }}</button>
        </form>
        @auth
            <p class="text-sm text-gray-600">{{ __('Reserving follows this list automatically. You can turn updates off in Followed lists.') }} <a class="inline-flex min-h-11 items-center text-coral-700" href="{{ route('friends.index', ['locale' => app()->getLocale()]) }}">{{ __('Followed lists') }}</a></p>
        @endauth
        <p class="text-sm text-gray-500">{{ __('So nobody else buys it. You still buy it yourself in the shop.') }}</p>
    @endif
    @if($list && ! $claim)<a x-show="! $refs.dialog" href="{{ $list->getPublicUrl() }}" class="btn-cancel w-full text-center">{{ __('Back to wishlist') }}</a>@endif
</div>
