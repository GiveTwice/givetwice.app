{!! $title !!}

{!! __('Hi :name,', ['name' => $user->name]) !!}
{!! $intro !!}

{!! $actionText !!}: {!! $actionUrl !!}

@foreach($outroLines as $line)
{!! $line !!}
@endforeach

@include('emails.text.footer')
