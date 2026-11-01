{!! __("You're invited!", locale: $locale) !!}

{!! __('Hello', locale: $locale) !!},
{!! __(':name has invited you to collaborate on their wishlist:', ['name' => $inviter->name], $locale) !!}
{!! str_replace("\u{2014}", '·', $list->name) !!}
{!! $list->description !!}

{!! __("You'll be able to add and edit gifts on this list together.", locale: $locale) !!}
@if($isExistingUser)
{!! __('Accept invitation', locale: $locale) !!}: {!! $acceptUrl !!}
{!! __('You can also accept or decline this invitation from your dashboard.', locale: $locale) !!}
@else
{!! __('Create account & accept', locale: $locale) !!}: {!! $registerUrl !!}
{!! __('Create a free account and start adding gifts together.', locale: $locale) !!}
@endif

{!! __('This invitation expires in 30 days.', locale: $locale) !!}

@include('emails.text.footer')
