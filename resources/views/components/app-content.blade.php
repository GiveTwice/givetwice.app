@props([
    'title' => null,
    'description' => null,
    'breadcrumbs' => [],
    'fullWidth' => false,
])

<div class="bg-white rounded-2xl shadow-sm border border-cream-200/60 overflow-hidden">

    <div class="px-6 sm:px-8 pt-6 sm:pt-8 pb-6 border-b border-gray-100">

        @if(count($breadcrumbs) > 0)
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                @foreach($breadcrumbs as $index => $crumb)
                    @if($index > 0)
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
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
                        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                    @endif
                    @if($description)
                        <p class="text-gray-600 mt-1">{{ $description }}</p>
                    @endif
                @endisset
            </div>

            @isset($actions)
                <div class="flex items-center gap-2 flex-shrink-0">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>

    <div class="{{ $fullWidth ? '' : 'px-6 sm:px-8 py-6 sm:py-8' }}">
        {{ $slot }}
    </div>
</div>
