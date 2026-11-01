@extends('layouts.app')

@section('content')
    <div class="max-w-lg mx-auto py-8 px-4">
        <h1 class="text-xl font-semibold">{{ __('exchange-reminders.title') }}</h1>
        @if($participant->reminders_muted_at)
            <p class="mt-4">{{ __('exchange-reminders.muted') }}</p>
        @else
            <form method="POST" class="mt-4">
                @csrf
                <button type="submit" class="btn-primary min-h-11">{{ __('exchange-reminders.stop') }}</button>
            </form>
        @endif
    </div>
@endsection
