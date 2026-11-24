<form method="POST" action="{{ route('reservations.link', ['locale' => app()->getLocale()]) }}" class="flex flex-col gap-3">@csrf
    <label for="email" class="form-label mb-0">{{ __('Email') }}</label><input id="email" name="email" type="email" required autocomplete="email" class="form-input" value="{{ old('email') }}">
    @error('email')<p class="text-red-700">{{ $message }}</p>@enderror
    <button class="btn-primary justify-center">{{ __('reservations.send') }}</button>
</form>
