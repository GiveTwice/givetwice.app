@if($alert->after['deleted'])
    <p>{{ __('reservations.keep_done', ['owner' => $alert->before['owner']]) }}</p>
@else
    @foreach(['title', 'price', 'url', 'owner_note'] as $field)
        <h3 style="color: #374151; font-size: 14px; line-height: 1.6; font-weight: 600; margin: 16px 0 4px;">{{ __('reservations.'.$field) }}</h3>
        <p style="margin: 0; font-size: 14px;">{{ __('reservations.before') }}: {{ $alert->before[$field] ?: __('reservations.none') }}</p>
        <p style="margin: 0; font-size: 14px;">{{ __('reservations.after') }}: {{ $alert->after[$field] ?: __('reservations.none') }}</p>
    @endforeach
@endif
