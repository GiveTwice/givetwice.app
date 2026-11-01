@if($alert->after['deleted'])
    <p>{{ __('reservations.removed_summary', ['list' => $alert->before['list_name'] ?? $alert->before['owner']]) }}</p>
@else
    @foreach($alert->changedFields() as $field => $values)
        <h3 style="color: #374151; font-size: 14px; line-height: 1.6; font-weight: 600; margin: 16px 0 4px;">{{ __('reservations.'.$field) }}</h3>
        <p style="margin: 0; font-size: 14px;">{{ __('reservations.before') }}: {{ $values['before'] ?: __('reservations.none') }}</p>
        <p style="margin: 0; font-size: 14px;">{{ __('reservations.after') }}: {{ $values['after'] ?: __('reservations.none') }}</p>
    @endforeach
@endif
