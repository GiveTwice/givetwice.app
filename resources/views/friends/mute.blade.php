@extends('layouts.app')
@section('content')
<x-page-card icon="🔕" :title="$follow->list->name">
    @if($follow->notifications)
        <form method="POST">@csrf<button class="btn-primary min-h-11 w-full justify-center">{{ __('list-updates.stop') }}</button></form>
    @else
        <p class="text-gray-600">{{ __('list-updates.stopped') }}</p>
    @endif
</x-page-card>
@endsection
