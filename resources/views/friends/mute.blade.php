@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-lg px-4 py-8">
    <h1 class="text-xl font-semibold">{{ $follow->list->name }}</h1>
    @if($follow->notifications)
        <form method="POST" class="mt-4">@csrf<button class="btn-primary min-h-11">{{ __('list-updates.stop') }}</button></form>
    @else
        <p class="mt-4">{{ __('list-updates.stopped') }}</p>
    @endif
</div>
@endsection
