{!! __('Reserved for you') !!}

{!! __('Nobody else will buy this now. The surprise is safe. You still buy it yourself in the shop.') !!}

@foreach($claims as $reserved)
{!! $reserved->gift->title !!}
{!! $reserved->gift->formatPrice() !!} {!! $reserved->gift->siteName() !!}
@if($reserved->gift->url)
{!! __('Go to the shop') !!}: {!! $reserved->gift->url !!}
@else
{!! __('Buy it wherever you like.') !!}
@endif
{!! __('Undo reservation') !!}: {!! $undoUrls->get($reserved->id) !!}

@endforeach
{!! __('All my reservations') !!}: {!! $listUrl !!}
{!! __('The gift will be free again.') !!}

@include('emails.text.footer')
