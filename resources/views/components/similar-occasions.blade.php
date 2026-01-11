@props(['occasions' => [], 'currentOccasion' => null])

@php
    use App\Helpers\OccasionHelper;

    $locale = app()->getLocale();

    $filteredOccasions = collect($occasions)
        ->filter(fn($occasion) => $occasion !== $currentOccasion && OccasionHelper::shouldShow($occasion, $locale))
        ->take(4)
        ->all();
@endphp

@if(count($filteredOccasions) > 0)
<div class="bg-cream-50 rounded-2xl p-8 lg:p-10 mb-12">
    <h2 class="text-xl font-bold text-gray-900 mb-6 text-center">{{ __('Planning ahead?') }}</h2>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($filteredOccasions as $occasionKey)
            @php $data = OccasionHelper::get($occasionKey); @endphp
            @if($data)
                <a href="{{ route("occasion.{$occasionKey}", ['locale' => $locale]) }}"
                   class="bg-white rounded-xl p-4 border border-cream-200 hover:border-coral-200 hover:shadow-sm transition-all text-center group">
                    <div class="text-2xl mb-2">{{ $data['emoji'] }}</div>
                    <p class="font-medium text-gray-800 group-hover:text-coral-600 transition-colors text-sm">
                        {{ __($data['page_title']) }}
                    </p>
                </a>
            @endif
        @endforeach
    </div>
</div>
@endif
