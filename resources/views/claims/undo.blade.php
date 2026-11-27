@extends('layouts.app')
@section('title', __('Undo reservation'))
@section('robots', 'noindex, nofollow')
@section('content')
@php
    $item = $claim->reservation ?? [];
    $title = $item['title'] ?? $claim->gift?->title;
    $image = $claim->gift?->hasImage() ? $claim->gift->getImageUrl('thumb') : ($item['image'] ?? null);
    $occasion = $item['occasion'] ?? ['type' => null, 'date' => $item['date'] ?? null];
    $occasionText = collect([
        \App\Models\GiftList::occasionLabel($occasion['type']),
        $occasion['date'] ? \Illuminate\Support\Carbon::parse($occasion['date'])->translatedFormat('j F Y') : null,
    ])->filter()->implode(' · ');
@endphp
<x-page-card icon="↩️" :title="__('Undo this reservation?')">
    <div class="flex flex-col gap-4 text-sm">
        <div class="flex items-center gap-3">
            @if($image)<img src="{{ $image }}" alt="" class="h-16 w-16 shrink-0 rounded-lg border border-cream-200 bg-white object-contain">@endif
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
                @if(! empty($item['price']))<p class="text-gray-500">{{ $item['price'] }}</p>@endif
            </div>
        </div>
        <dl class="grid grid-cols-[auto_minmax(0,1fr)] gap-x-4 gap-y-2 rounded-xl bg-cream-50 p-3">
            @if(! empty($item['owner']))
                <dt class="text-gray-500">{{ __('For') }}</dt>
                <dd class="font-medium text-gray-900">{{ $item['owner'] }}</dd>
            @endif
            @if(! empty($item['list_name']))
                <dt class="text-gray-500">{{ __('Wishlist') }}</dt>
                <dd class="font-medium text-gray-900">
                    @if($list)<a href="{{ $list->getPublicUrl() }}" class="text-coral-700 underline-offset-2 hover:underline">{{ $item['list_name'] }}</a>@else{{ $item['list_name'] }}@endif
                </dd>
            @endif
            @if($occasionText !== '')
                <dt class="text-gray-500">{{ __('Occasion') }}</dt>
                <dd class="font-medium text-gray-900">{{ $occasionText }}</dd>
            @endif
            <dt class="text-gray-500">{{ __('Reserved on') }}</dt>
            <dd class="font-medium text-gray-900">{{ ($claim->confirmed_at ?? $claim->created_at)->translatedFormat('j F Y') }}</dd>
        </dl>
        <div class="flex flex-col gap-1">
            <form method="POST" action="{{ request()->fullUrl() }}">@csrf<button class="btn-primary w-full">{{ __('Undo reservation') }}</button></form>
            <a href="{{ $keepUrl }}" class="btn-cancel w-full">{{ __('Keep my reservation') }}</a>
        </div>
    </div>
</x-page-card>
@endsection
