@extends('layouts.app')

@section('content')
<x-page-card icon="🔕" :title="__('exchange-reminders.title')">
    @if($participant->reminders_muted_at)
        <p class="text-gray-600">{{ __('exchange-reminders.muted') }}</p>
    @else
        <form method="POST">
            @csrf
            <button type="submit" class="btn-primary min-h-11 w-full justify-center">{{ __('exchange-reminders.stop') }}</button>
        </form>
    @endif
</x-page-card>
@endsection
