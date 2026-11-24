@props(['list'])

<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('list.edit', ['locale' => app()->getLocale(), 'list' => $list]) }}" class="btn-secondary" title="{{ __('Edit') }}">
        <x-icons.edit class="w-5 h-5" />
        <span>{{ __('Edit') }}</span>
    </a>
    <x-collaborators-button :list="$list" :show-label="true" />
    <x-share-modal :list="$list" :show-label="true" />
    @if($list->gifts->isNotEmpty())
        <a href="{{ route('gifts.create', ['locale' => app()->getLocale(), 'list' => $list->id]) }}" class="btn-primary">
            <x-icons.plus class="w-5 h-5" />
            {{ __('Add Gift') }}
        </a>
    @endif
</div>
