@extends('layouts.app')
@section('title', __('Make or link my wishlist'))
@section('content')
<x-page-card icon="🎁" :title="__('And what would you like?')" :subtitle="__('Link your list so whoever drew you knows what to buy.')">
    @if($errors->any())<p class="alert-error mb-4">{{ $errors->first() }}</p>@endif
    <form method="POST" action="{{ route('exchanges.wishlist.store', [app()->getLocale(), $participant->token]) }}" class="grid gap-4">
        @csrf
        <label class="form-label">{{ __('Choose a wishlist') }}
            <select name="wishlist_id" class="form-select">
                <option value="">{{ __('Create a new list') }}</option>
                @foreach($lists as $list)<option value="{{ $list->id }}">{{ $list->name }}</option>@endforeach
            </select>
        </label>
        <label class="form-label">{{ __('New list name') }}<input name="name" class="form-input" value="{{ old('name') }}"></label>
        <button class="btn-primary justify-center">{{ __('Link my wishlist') }}</button>
    </form>
</x-page-card>
@endsection
