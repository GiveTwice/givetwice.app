@extends('layouts.app')
@section('title', __('Undo reservation'))
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto flex max-w-md flex-col gap-4 rounded-xl bg-white p-5">
    <h1 class="text-lg font-bold">{{ __('Undo reservation') }}</h1>
    <p>{{ $claim->gift->title }}</p>
    <form method="POST" action="{{ request()->fullUrl() }}">@csrf<button class="btn-primary">{{ __('Undo reservation') }}</button></form>
</div>
@endsection
