@props(['invitations'])

@if($invitations->isNotEmpty())
    <div class="bg-teal-50 border-b border-teal-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            @foreach($invitations as $invitation)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-b border-teal-100' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center">
                            <x-icons.users class="w-4 h-4 text-teal-600" />
                        </div>
                        <div>
                            <p class="text-sm text-teal-900">
                                <strong>{{ $invitation->inviter->name }}</strong>
                                {{ __('invited you to collaborate on') }}
                                <strong>"{{ $invitation->list->name }}"</strong>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:flex-shrink-0">
                        <form action="{{ route('lists.invitation.accept', ['locale' => app()->getLocale(), 'token' => $invitation->token]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-teal-500 text-white text-sm font-medium rounded-lg hover:bg-teal-600 transition-colors">
                                {{ __('Accept') }}
                            </button>
                        </form>
                        <form action="{{ route('lists.invitation.decline', ['locale' => app()->getLocale(), 'token' => $invitation->token]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-gray-500 text-sm hover:text-gray-700 transition-colors">
                                {{ __('Decline') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
