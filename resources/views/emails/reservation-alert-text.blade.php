{!! __('reservations.updates') !!}
{!! str_replace("\u{2014}", '·', $alert->before['list_name'] ?? $alert->before['owner']) !!} · {!! $alert->before['owner'] !!}

@foreach($alerts as $alert)
{!! $alert->before['title'] !!}
@if($alert->after['deleted'])
{!! __('reservations.keep_done', ['owner' => $alert->before['owner']]) !!}
@else
@foreach(['title', 'price', 'url', 'owner_note'] as $field)
{!! __('reservations.'.$field) !!}
{!! __('reservations.before') !!}: {!! $alert->before[$field] ?: __('reservations.none') !!}
{!! __('reservations.after') !!}: {!! $alert->after[$field] ?: __('reservations.none') !!}

@endforeach
@endif
@foreach($alert->after['deleted'] ? ['keep', 'alternative', 'dismiss'] : ['new', 'old', 'release'] as $choice)
{!! __('reservations.'.$choice) !!}: {!! $alert->choiceUrl($choice) !!}
@endforeach

@endforeach
{!! __('All my reservations') !!}: {!! $alert->claim->historyUrl($alert->locale) !!}
{!! __('reservations.mute', ['owner' => $alert->before['list_name'] ?? $alert->before['owner']]) !!}: {!! $alert->choiceUrl('mute') !!}
