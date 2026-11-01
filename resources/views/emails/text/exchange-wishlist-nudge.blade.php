{!! __('Quick hint for whoever drew you') !!}

{!! __('Hey') !!} {!! $participant->name !!},
{!! __('exchange-reminders.wishlist', ['group' => str_replace("\u{2014}", '·', $exchange->name)]) !!}

{!! __('Make a wishlist') !!}: {!! $registerUrl !!}

{!! __('exchange-reminders.stop') !!}: {!! $participant->reminderMuteUrl() !!}
