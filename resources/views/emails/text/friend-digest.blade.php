{!! __('Wishlist updates') !!}

{!! __('Hello') !!} {!! $user->name !!},
{!! __('list-updates.intro') !!}

@foreach($digestData as $data)
{!! str_replace("\u{2014}", '·', $data['list']->name) !!}
{!! __('by') !!} {!! $data['list']->creator->name !!}

@foreach([__('Added') => $data['added_gifts'], __('list-updates.edited') => $data['edited_gifts'] ?? collect(), __('Removed') => $data['removed_gifts']] as $label => $gifts)
@if($gifts->isNotEmpty())
{!! $label !!}
@foreach($gifts as $gift)
- {!! $gift->title ?: __('Untitled gift') !!}
@endforeach

@endif
@endforeach
{!! __('list-updates.view') !!}: {!! $data['list']->getPublicUrl($locale) !!}
@isset($data['mute_url'])
{!! __('list-updates.stop') !!}: {!! $data['mute_url'] !!}
@endisset

@endforeach
{!! __('list-updates.settings') !!}: {!! $settingsUrl !!}#notifications

@include('emails.text.footer')
