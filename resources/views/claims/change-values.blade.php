<table style="width:100%;table-layout:fixed;border-collapse:collapse;font-size:13px;overflow-wrap:anywhere;word-break:break-word">
    <thead><tr><th></th><th>{{ __('reservations.before') }}</th><th>{{ __('reservations.after') }}</th></tr></thead>
    <tbody>
        @foreach(['title', 'price', 'url', 'owner_note'] as $field)
            <tr><th style="text-align:left;vertical-align:top;padding:8px 3px">{{ __('reservations.'.$field) }}</th><td style="vertical-align:top;padding:8px 3px">{{ $alert->before[$field] ?: __('reservations.none') }}</td><td style="vertical-align:top;padding:8px 3px">{{ $alert->after['deleted'] ? __('reservations.deleted', ['owner' => $alert->before['owner']]) : ($alert->after[$field] ?: __('reservations.none')) }}</td></tr>
        @endforeach
    </tbody>
</table>
