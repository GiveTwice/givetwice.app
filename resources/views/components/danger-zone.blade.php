@props([
    'id',
    'title' => __('Danger Zone'),
    'description',
    'buttonText',
    'modalTitle',
    'modalMessage',
    'action',
])

<div class="mt-8 bg-white/60 backdrop-blur-sm rounded-2xl border border-red-200/60 p-6" x-data>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-red-600">{{ $title }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ $description }}</p>
        </div>
        <button
            type="button"
            x-on:click="$dispatch('open-confirm-{{ $id }}')"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-5 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium whitespace-nowrap"
        >
            <x-icons.trash class="w-5 h-5" />
            {{ $buttonText }}
        </button>
    </div>
</div>

<x-confirm-modal
    :id="$id"
    :title="$modalTitle"
    :message="$modalMessage"
    :confirmText="$buttonText"
>
    <form method="POST" action="{{ $action }}">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium"
        >
            <x-icons.trash class="w-5 h-5" />
            {{ $buttonText }}
        </button>
    </form>
</x-confirm-modal>
