{!! __('reservations.updates') !!}
{!! str_replace("\u{2014}", '·', $alert->before['list_name'] ?? $alert->before['owner']) !!} · {!! $alert->before['owner'] !!}

@foreach($alerts as $alert)
{!! $alert->before['title'] !!}
@if($alert->after['deleted'])
{!! __('reservations.removed_summary', ['list' => $alert->before['list_name'] ?? $alert->before['owner']]) !!}
@else
@foreach($alert->changedFields() as $field => $values)
{!! __('reservations.'.$field) !!}
{!! __('reservations.before') !!}: {!! $values['before'] ?: __('reservations.none') !!}
{!! __('reservations.after') !!}: {!! $values['after'] ?: __('reservations.none') !!}

@endforeach
@endif
@foreach($alert->after['deleted'] ? ['keep', 'alternative', 'dismiss'] : ['new', 'old', 'release'] as $choice)
{!! __('reservations.'.$choice) !!}: {!! $alert->choiceUrl($choice) !!}
@endforeach

@endforeach
{!! __('All my reservations') !!}: {!! $alert->claim->historyUrl($alert->locale) !!}
{!! __('list-updates.stop') !!}: {!! $alert->choiceUrl('mute') !!}

@include('emails.text.footer')
