@if (config('services.turnstile.site_key'))
    <div class="min-h-[65px]" x-data="turnstile(@js(config('services.turnstile.site_key')), @js(app()->getLocale()))"></div>
@endif
