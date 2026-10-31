@extends('layouts.app')
@section('title', __('Your first gift is on the list!'))
@section('robots', 'noindex, nofollow')
@section('content')
@php
    $publicUrl = $list->getPublicUrl();
    $message = __('You can view my wish list here:') . ' ' . $publicUrl;
@endphp
<div class="mx-auto w-full max-w-lg px-5 py-10" x-data="{
    copied: false,
    async copy() {
        try { await navigator.clipboard.writeText(@js($publicUrl)); this.copied = true; }
        catch { $refs.link.hidden = false; $refs.link.select(); }
    },
    async more() {
        try { await navigator.share({ title: @js($list->name), url: @js($publicUrl) }); }
        catch (error) { if (error.name !== 'AbortError') this.copy(); }
    }
}">
    <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="btn-link">{{ __('Later') }}</a>
    <h1 class="mt-6 text-2xl font-bold">{{ __('Your first gift is on the list!') }}</h1>
    <p class="mt-3 text-gray-600">{{ __('Send your list to the family. Add more gifts later: they will see those too.') }}</p>
    <div class="my-6 rounded-xl bg-teal-50 p-3">
        <div class="overflow-hidden rounded-xl border border-teal-100 bg-white">
            <img x-on:error.once="$el.src = @js(asset('images/og-image.png'))" src="{{ $imageUrl }}" alt="" class="aspect-[1200/630] w-full object-contain">
            <div class="p-3"><p class="font-semibold break-words">{{ $list->name }}</p><p class="text-sm text-gray-500">{{ trans_choice(':count gift|:count gifts', $list->gifts()->count()) }} · givetwice.app</p></div>
        </div>
    </div>
    <a href="https://wa.me/?text={{ rawurlencode($message) }}" target="_blank" rel="noopener" class="flex w-full items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-semibold text-white">{{ __('Send via WhatsApp') }}</a>
    <div class="mt-3 flex gap-3">
        <button @click="copy()" class="btn-secondary flex-1 justify-center"><span x-text="copied ? @js(__('Copied!')) : @js(__('Copy link'))"></span></button>
        <button x-show="!!navigator.share" @click="more()" class="btn-secondary flex-1 justify-center">{{ __('More...') }}</button>
    </div>
    <input x-ref="link" hidden readonly value="{{ $publicUrl }}" class="form-input mt-3" aria-label="{{ __('Public Link') }}">
    <p class="mt-5 text-center text-sm text-gray-500">{{ __('Anyone with the link can view the list.') }}</p>
</div>
@endsection
