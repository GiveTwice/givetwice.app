{!! __("We haven't seen you in a while") !!}

{!! __('Hi :name,', ['name' => $user->name]) !!}
{!! __('Your GiveTwice account has been quiet for a while. We clean up inactive accounts after 24 months to protect your privacy.') !!}

{!! __('What happens next?') !!}
{!! __('If you don\'t log in within 2 months, your account and data will be permanently deleted.') !!}

{!! __('Want to keep it? Just log in and your account stays active.') !!}
{!! __('Log in to keep your account') !!}: {!! $loginUrl !!}

{!! __('You can also export your data before deletion from your') !!} {!! __('settings page') !!}: {!! $settingsUrl !!}
