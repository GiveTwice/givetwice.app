@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
<div class="bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">{{ __('Dashboard') }}</h1>

    <p class="text-gray-600 mb-4">
        {{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}
    </p>

    <p class="text-gray-600">
        {{ __('Your email is verified.') }}
    </p>
</div>
@endsection
