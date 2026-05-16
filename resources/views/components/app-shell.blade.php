@props([
    'title' => __('Inside GiveTwice'),
    'description' => null,
    'sidebarItems' => [],
    'actions' => [],
    'stats' => [],
])

<div class="grid gap-6 app-shell-layout-cozy">
    <aside class="self-start rounded-[1.75rem] border border-cream-200 bg-white shadow-sm lg:sticky lg:top-[5.5625rem]">
        <div class="rounded-t-[1.75rem] border-b border-cream-200 bg-cream-50 px-5 py-5">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-coral-600">{{ __('App') }}</p>
            <h2 class="mt-2 text-xl font-semibold text-gray-900">{{ $title }}</h2>
            @if($description)
                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $description }}</p>
            @endif
        </div>

        @if($stats !== [])
            <div class="grid grid-cols-3 gap-3 border-b border-cream-200 px-5 py-4">
                @foreach($stats as $stat)
                    <div class="rounded-2xl border border-cream-200 bg-cream-50 px-3 py-3">
                        <p class="text-lg font-semibold text-gray-900">{{ $stat['value'] }}</p>
                        <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <nav class="px-4 pt-4 pb-4">
            <div class="space-y-2">
                @foreach($sidebarItems as $item)
                    @php $active = (bool) ($item['active'] ?? false); @endphp
                    <a
                        href="{{ $item['href'] ?? null }}"
                        class="flex items-center gap-3 rounded-2xl border px-3 py-3 transition-colors duration-150 {{ $active ? 'border-cream-200 bg-cream-50 text-gray-900' : 'border-transparent bg-white hover:border-cream-200 hover:bg-cream-50/70' }}"
                    >
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl {{ $active ? 'bg-coral-100 text-coral-600' : 'bg-cream-100 text-gray-600' }}">
                            @if(isset($item['icon']))
                                <x-dynamic-component :component="'icons.'.$item['icon']" class="h-5 w-5" />
                            @else
                                <span class="text-lg leading-none">{{ $item['emoji'] ?? '•' }}</span>
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-gray-900">{{ $item['label'] }}</span>
                            @if(isset($item['meta']))
                                <span class="block truncate text-xs text-gray-500">{{ $item['meta'] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </nav>

        @if($actions !== [])
            <div class="border-t border-cream-200 px-4 py-4">
                <p class="px-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">{{ __('Quick actions') }}</p>
                <div class="mt-3 space-y-2">
                    @foreach($actions as $action)
                        <a href="{{ $action['href'] ?? null }}" class="{{ ($action['style'] ?? 'primary') === 'primary' ? 'btn-primary w-full justify-center' : 'btn-secondary w-full justify-center' }}">
                            @if(isset($action['icon']))
                                <x-dynamic-component :component="'icons.'.$action['icon']" class="h-4 w-4" />
                            @elseif(isset($action['emoji']))
                                <span>{{ $action['emoji'] }}</span>
                            @endif
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </aside>

    <div class="min-w-0">
        {{ $slot }}
    </div>
</div>
