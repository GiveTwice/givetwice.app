{!! __('You\'ve been drawn!') !!}

{!! __('Hey') !!} {!! $participant->name !!},
{!! $exchange->organizer->name !!} {!! __('started a gift exchange for') !!} {!! str_replace("\u{2014}", '·', $exchange->name) !!} {!! __('and you got picked.') !!}
{!! __('Tap below to see who you\'re buying for.') !!}

@if($exchange->formatBudget())
{!! __('Budget') !!}: {!! $exchange->formatBudget() !!}
@endif
@if($exchange->event_date)
{!! $exchange->event_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY') !!}
@endif

{!! __('See my draw') !!}: {!! $revealUrl !!}
{!! __('No account needed. Just click and see.') !!}
