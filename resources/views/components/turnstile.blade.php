@if (config('services.turnstile.site_key'))
    <div x-data="turnstile(@js(config('services.turnstile.site_key')), @js(app()->getLocale()))" x-show="visible"></div>
@endif
