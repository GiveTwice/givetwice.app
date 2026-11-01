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
    @else
        @if($claim)<x-section-heading x-text="editing ? @js(__('Change email')) : @js(__('Reserved for you'))"></x-section-heading>@endif
        @if($gift->hasImage())
            <img src="{{ $gift->getImageUrl('card') }}" alt="" class="w-full rounded-xl bg-cream-50 object-contain {{ $claim ? 'h-16' : 'h-32' }}">
        @elseif(!$claim)
            <div class="flex h-20 items-center justify-center rounded-xl bg-cream-100 text-gray-500">{{ __('No image') }}</div>
        @endif
        <h3 class="text-base font-semibold text-gray-900">{{ $gift->title }}</h3>
        <div class="flex flex-wrap items-center justify-between gap-2 text-gray-500">
            <span>{{ $gift->formatPrice() }}{{ $gift->hasPrice() && $gift->siteName() ? ' · ' : '' }}{{ $gift->siteName() }}</span>
            @if($gift->url && !$claim)<a class="btn-link" href="{{ $gift->url }}" target="_blank" rel="noopener noreferrer">{{ __('View in shop') }} ↗</a>@endif
        </div>
        @unless($gift->url)<p class="text-gray-500">{{ __('Buy it wherever you like.') }}</p>@endunless
        @if($gift->owner_note && !$claim)
            <div class="rounded-lg bg-cream-100 p-3"><p class="text-sm font-semibold text-gray-500">{{ __('Note from :name', ['name' => $list?->creator?->name ?? $gift->user->name]) }}</p>{{ $gift->owner_note }}</div>
        @endif
        @if($claim)
            <div x-show="!editing" class="flex flex-col gap-2 rounded-lg border border-cream-200 p-3">
                <p>{{ __('Confirmation to') }} <strong>{{ $email }}</strong></p>
                <div class="flex flex-wrap justify-between gap-2"><button type="button" x-on:click="editing = true; $nextTick(() => $refs.email.focus())" class="btn-link">{{ __('Change email') }}</button><form method="POST" action="{{ route('claim.cancel', $parameters) }}" x-on:submit.prevent="$dispatch('reservation-submit', {form: $el})">@csrf @method('DELETE')<button>{{ __('Undo reservation') }}</button></form></div>
            </div>
            @if($suggestion)<button type="button" class="text-left text-coral-600" x-on:click="editing = true; email = @js($suggestion)">{{ __('Did you mean :email?', ['email' => $suggestion]) }}</button>@endif
            <form x-show="!editing" method="POST" action="{{ route('claim.resend', $parameters) }}" x-on:submit.prevent="$dispatch('reservation-submit', {form: $el})">@csrf<span class="text-sm text-gray-500">{{ __('No email after five minutes?') }}</span> <button class="btn-link">{{ __('Send again') }}</button></form>
        @endif
        <form x-show="{{ $claim ? 'editing' : 'true' }}" method="POST" action="{{ route($claim ? 'claim.email' : 'claim.anonymous.store', $parameters) }}" x-on:submit.prevent="$dispatch('reservation-submit', {form: $el})" class="flex flex-col gap-2">
            @csrf @if($claim) @method('PATCH') @endif
            @if($claim || !auth()->check())
                <label for="reservation-email" class="sr-only">{{ __('Email') }}</label>
                <input id="reservation-email" x-ref="email" x-model="email" type="email" name="email" required maxlength="255" autocomplete="email" placeholder="{{ __('Your email address') }}" class="form-input w-full min-w-0">
                @if($suggestion)<button type="button" x-on:click="email = @js($suggestion)" class="text-left text-coral-600">{{ $suggestion }}</button>@endif
            @endif
            <button class="btn-primary w-full" :disabled="busy">{{ $claim ? __('Save and send again') : __('Reserve this gift') }}</button>
            @if($claim)<button type="button" x-on:click="editing = false" class="btn-cancel">{{ __('Cancel') }}</button>@endif
        </form>
        @if($claim)<form x-show="editing" method="POST" action="{{ route('claim.cancel', $parameters) }}" x-on:submit.prevent="$dispatch('reservation-submit', {form: $el})">@csrf @method('DELETE')<button class="text-gray-500">{{ __('Undo reservation') }}</button></form>@endif
        @auth
            <p class="text-sm text-gray-600">{{ __('Reserving follows this list automatically. You can turn updates off in Followed lists.') }} <a class="inline-flex min-h-11 items-center text-coral-700" href="{{ route('friends.index', ['locale' => app()->getLocale()]) }}">{{ __('Followed lists') }}</a></p>
        @endauth
        @if(!$claim)
            <p class="text-sm text-gray-500">{{ __('So nobody else buys it. You still buy it yourself in the shop.') }}</p>
            @guest
                @if($email)<p x-show="email" class="text-sm text-gray-500">{{ __('Your email is ready from last time.') }} <button type="button" class="btn-link" x-on:click="forgetEmail(@js(route('claim.forget-email', ['locale' => app()->getLocale()]))); email = ''">{{ __('Not you?') }}</button></p>@endif
            @endguest
        @else
            @if($gift->url)<a x-show="!editing" href="{{ $gift->url }}" target="_blank" rel="noopener noreferrer" class="btn-primary w-full">{{ __('Go to the shop') }} ↗</a>@endif
            @guest
                @if(config('app.allow_registration'))
                    <div x-show="!editing" class="order-last border-t border-cream-200 pt-3 text-sm"><p class="font-semibold">{{ __('Something on your own wishlist?') }}</p><p class="text-gray-500">{{ __('Your email is ready. One minute of work.') }}</p><a href="{{ route('register', ['locale' => app()->getLocale(), 'email' => $email]) }}" class="btn-link">{{ __('Make my wishlist') }} →</a></div>
                @endif
            @endguest
        @endif
    @endif
    @if($list)<a href="{{ $list->getPublicUrl() }}" x-on:click="if ($refs.dialog) { $event.preventDefault(); close(); }" class="btn-cancel w-full text-center">{{ __('Back to wishlist') }}</a>@endif
</div>
