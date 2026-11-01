@if($alert->after['deleted'])
    <p>{{ __('reservations.removed_summary', ['list' => $alert->before['list_name'] ?? $alert->before['owner']]) }}</p>
@else
    <table style="width:100%;table-layout:fixed;border-collapse:collapse;font-size:14px;overflow-wrap:anywhere;word-break:break-word">
        <thead><tr><th></th><th style="text-align:left;padding:8px 3px;font-weight:600">{{ __('reservations.before') }}</th><th style="text-align:left;padding:8px 3px;font-weight:600">{{ __('reservations.after') }}</th></tr></thead>
        <tbody>
            @foreach($alert->changedFields() as $field => $values)
                <tr><th style="text-align:left;font-weight:600;vertical-align:top;padding:8px 3px">{{ __('reservations.'.$field) }}</th><td style="vertical-align:top;padding:8px 3px">{{ $values['before'] ?: __('reservations.none') }}</td><td style="vertical-align:top;padding:8px 3px">{{ $values['after'] ?: __('reservations.none') }}</td></tr>
            @endforeach
        </tbody>
    </table>
@endif
