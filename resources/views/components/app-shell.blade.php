@props([
    'title' => __('Inside GiveTwice'),
    'description' => null,
    'sidebarItems' => [],
    'actions' => [],
    'stats' => [],
])

<div class="grid gap-5 app-shell-layout-cozy">
    <aside class="hidden lg:block self-start rounded-2xl border border-stone-200/80 bg-white shadow-sm lg:sticky lg:top-[5.5625rem]">
        <div class="rounded-t-2xl border-b border-cream-200 bg-cream-50 px-4 py-3.5">
            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.18em] text-coral-600">{{ __('App') }}</p>
            <h2 class="mt-0.5 text-[1.0625rem] font-semibold text-gray-900">{{ $title }}</h2>
            @if($description)
                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $description }}</p>
            @endif
        </div>

        @if($stats !== [])
            <div class="grid grid-cols-3 gap-2 border-b border-cream-200 px-4 py-3">
                @foreach($stats as $stat)
                    <div class="rounded-[10px] border border-cream-200 bg-cream-50 px-2 py-2">
                        <p class="text-base font-semibold text-gray-900">{{ $stat['value'] }}</p>
                        <p class="truncate text-[0.6875rem] text-gray-500">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <nav class="p-2.5">
            <div class="space-y-0.5">
                @foreach($sidebarItems as $item)
                    @php $active = (bool) ($item['active'] ?? false); @endphp
                    <a
                        href="{{ $item['href'] ?? null }}"
                        class="flex items-center gap-2.5 rounded-[10px] border px-2.5 py-2 transition-colors duration-150 {{ $active ? 'border-cream-200 bg-cream-50 text-gray-900' : 'border-transparent bg-white hover:border-cream-200 hover:bg-cream-50/70' }}"
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[9px] {{ $active ? 'bg-coral-100 text-coral-600' : 'bg-cream-100 text-gray-600' }}">
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
