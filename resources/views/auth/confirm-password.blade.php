@extends('layouts.guest')

@section('title', __('Confirm Password'))

@section('content')
<div class="bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold text-center mb-6">{{ __('Confirm Password') }}</h2>

    <p class="text-gray-600 mb-6 text-center">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/user/confirm-password') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">{{ __('Password') }}</label>
            <input type="password" name="password" id="password" required autofocus
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            {{ __('Confirm') }}
        </button>
    </form>
</div>
@endsection
