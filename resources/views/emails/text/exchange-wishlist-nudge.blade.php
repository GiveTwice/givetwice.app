{!! __('exchange-reminders.wishlist_title') !!}

{!! __('Hey') !!} {!! $participant->name !!},
{!! __('exchange-reminders.wishlist', ['group' => str_replace("\u{2014}", '·', $exchange->name)]) !!}

{!! __('Make a wishlist') !!}: {!! $registerUrl !!}

{!! __('exchange-reminders.stop') !!}: {!! $participant->reminderMuteUrl() !!}

@include('emails.text.footer')
