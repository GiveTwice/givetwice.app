@props([
    'title' => null,
    'description' => null,
    'breadcrumbs' => [],
    'fullWidth' => false,
])

<div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 overflow-hidden">

    <div class="px-4 sm:px-6 pt-4 sm:pt-5 pb-3.5 sm:pb-4 border-b border-gray-100">

        @if(count($breadcrumbs) > 0)
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                @foreach($breadcrumbs as $index => $crumb)
                    @if($index > 0)
                        <x-icons.chevron-right class="w-4 h-4 text-gray-400" />
                    @endif
                    @if(isset($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="hover:text-coral-600 transition-colors">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-gray-900 font-medium">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                @isset($titleSlot)

                    {{ $titleSlot }}
                @else
                    @if($title)
                        <h1 class="text-[1.375rem] sm:text-2xl font-bold tracking-tight text-gray-900">{{ $title }}</h1>
                    @endif
                    @if($description)
                        <p class="text-gray-600 mt-1">{{ $description }}</p>
                    @endif
                @endisset
            </div>

            @isset($actions)
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>

    <div class="{{ $fullWidth ? '' : 'p-4 sm:px-6 sm:py-5' }}">
        {{ $slot }}
    </div>
</div>
