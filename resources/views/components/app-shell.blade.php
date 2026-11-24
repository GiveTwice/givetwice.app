@props([
    'title' => __('Inside GiveTwice'),
    'description' => null,
    'sidebarItems' => [],
    'actions' => [],
    'stats' => [],
])

@php
    // Each section keeps one brand colour in its icon tile, its hover and its active state.
    // Shades that app.css redefines for dark mode are avoided where white text sits on them.
    $accents = [
        'coral' => ['tile' => 'bg-coral-100 text-coral-600', 'tileActive' => 'bg-coral-500 text-white', 'row' => 'hover:border-coral-100 hover:bg-coral-50', 'rowActive' => 'border-coral-200 bg-coral-50'],
        'sunny' => ['tile' => 'bg-sunny-100 text-sunny-800', 'tileActive' => 'bg-sunny-400 text-sunny-900', 'row' => 'hover:border-sunny-100 hover:bg-sunny-50', 'rowActive' => 'border-sunny-200 bg-sunny-50'],
        'teal' => ['tile' => 'bg-teal-100 text-teal-700', 'tileActive' => 'bg-teal-700 text-white', 'row' => 'hover:border-teal-100 hover:bg-teal-50', 'rowActive' => 'border-teal-200 bg-teal-50'],
        'gray' => ['tile' => 'bg-gray-100 text-gray-600', 'tileActive' => 'bg-[#374151] text-white', 'row' => 'hover:border-gray-200 hover:bg-gray-50', 'rowActive' => 'border-gray-200 bg-gray-50'],
    ];
    // The three counts take the brand colours in order, like the list cards on the dashboard.
    $statTiles = ['bg-coral-50 text-coral-700', 'bg-sunny-100 text-sunny-800', 'bg-teal-50 text-teal-700'];
@endphp

<div class="grid gap-5 app-shell-layout-cozy">
    <aside class="hidden lg:block self-start rounded-2xl border border-stone-200/80 bg-white shadow-sm lg:sticky lg:top-5">
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
                    <div class="rounded-[10px] px-2 py-2 {{ $statTiles[$loop->index % 3] }}">
                        <p class="text-base font-bold">{{ $stat['value'] }}</p>
                        <p class="truncate text-[0.6875rem] text-gray-600">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <nav class="p-2.5">
            <div class="space-y-0.5">
                @foreach($sidebarItems as $item)
                    @php
                        $active = (bool) ($item['active'] ?? false);
                        $accent = $accents[$item['accent'] ?? 'gray'];
                    @endphp
                    <a
                        href="{{ $item['href'] ?? null }}"
                        class="flex items-center gap-2.5 rounded-[10px] border px-2.5 py-2 transition-colors duration-150 {{ $active ? $accent['rowActive'] : 'border-transparent bg-white '.$accent['row'] }}"
                        @if($active) aria-current="page" @endif
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[9px] transition-colors duration-150 {{ $active ? $accent['tileActive'] : $accent['tile'] }}">
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
                    @isset($item['children'])
                        {{-- The 2px guide line is centred under the icon tile above, whose centre is 27px in (border 1px + padding 10px + half the 32px tile).
                             The 26px margin, the line and 25px padding put each dot on the same 53px left edge as the "Lists" label.
                             Each link reaches 8px out on both sides, so its hover background has room without moving the text. --}}
                        <div class="my-1 ml-[26px] flex flex-col gap-0.5 border-l-2 border-coral-200 pl-[25px] pr-2.5" data-sidebar-children>
                            @foreach($item['children']['links'] as $child)
                                <a href="{{ $child['href'] }}" class="-mx-2 flex items-baseline gap-2 rounded-lg px-2 py-1 text-sm transition-colors duration-150 {{ $child['active'] ? 'bg-coral-50 font-semibold text-gray-900' : 'text-gray-700 hover:bg-coral-50 hover:text-gray-900' }}" @if($child['active']) aria-current="page" @endif><span class="size-2 shrink-0 translate-y-[-1px] rounded-full {{ $child['dot'] }}"></span><span class="min-w-0 break-words">{{ $child['label'] }}</span></a>
                            @endforeach
                            @if($item['children']['moreHref'])
                                <a href="{{ $item['children']['moreHref'] }}" class="-mx-2 rounded-lg px-2 py-1 text-sm text-gray-600 hover:bg-coral-50 hover:text-gray-900">{{ __('All lists') }}</a>
                            @endif
                            <a href="{{ $item['children']['createHref'] }}" class="-mx-2 inline-flex items-center gap-[5px] rounded-lg px-2 py-1 text-sm font-medium text-coral-600 hover:bg-coral-50 hover:text-coral-700">
                                {{-- Pulled 3px left so the 14px plus is centred on the 8px dots above, and its label lines up with theirs. --}}
                                <x-icons.plus class="-ml-[3px] h-3.5 w-3.5" />
                                {{ __('New list') }}
                            </a>
                        </div>
                    @endisset
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
