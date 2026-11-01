{!! __('exchange-reminders.reveal_title') !!}

{!! __('Hey') !!} {!! $participant->name !!},
{!! __('exchange-reminders.reveal', ['group' => str_replace("\u{2014}", '·', $exchange->name)]) !!}

{!! __('See my draw') !!}: {!! $revealUrl !!}

@if($exchange->event_date)
{!! __('exchange-reminders.date', ['date' => $exchange->event_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY')]) !!}

@endif
{!! __('exchange-reminders.stop') !!}: {!! $participant->reminderMuteUrl() !!}
