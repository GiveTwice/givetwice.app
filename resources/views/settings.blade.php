@extends('layouts.app')

@section('title', __('Settings'))

@section('robots', 'noindex, nofollow')

@section('content')
<x-app-content
    :title="__('Settings')"
    :description="__('Manage your account settings and preferences.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => __('Settings')]
    ]"
>
    <div class="space-y-8">

        {{-- Profile Information --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="profileSettings()">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Profile information') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __("Update your account's profile information and photo.") }}</p>
            </div>

            <div class="lg:col-span-2">
                {{-- Profile Image Section --}}
                <div class="mb-8 pb-8 border-b border-gray-100">
                    <label class="form-label mb-4">{{ __('Profile photo') }}</label>

                    <div class="flex items-start gap-6">
                        {{-- Avatar Upload Area --}}
                        <div class="relative flex-shrink-0">
                            <input
                                type="file"
                                id="profile-image-upload"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                class="hidden"
                                x-ref="imageInput"
                                x-on:change="uploadImage($event)"
                            >

                            <div
                                class="group relative w-24 h-24 rounded-full overflow-hidden cursor-pointer ring-4 ring-cream-100 transition-all duration-300 hover:ring-coral-200"
                                x-on:click="$refs.imageInput.click()"
                            >
                                {{-- Avatar Image or Initials --}}
                                <template x-if="imageUrl">
                                    <img
                                        :src="imageUrl"
                                        :alt="'{{ auth()->user()->name }}'"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    >
                                </template>

                                <template x-if="!imageUrl">
                                    <div class="w-full h-full bg-gradient-to-br from-coral-400 to-coral-500 flex items-center justify-center">
                                        <span class="text-white text-2xl font-bold tracking-tight">{{ auth()->user()->getInitials() }}</span>
                                    </div>
                                </template>

                                {{-- Hover Overlay --}}
                                <div
                                    class="absolute inset-0 bg-gray-900/0 group-hover:bg-gray-900/40 transition-all duration-300 flex items-center justify-center"
                                    x-show="!uploading"
                                >
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white">
                                        <x-icons.image-placeholder class="w-6 h-6 mx-auto" />
                                    </div>
                                </div>

                                {{-- Upload Spinner --}}
                                <div
                                    class="absolute inset-0 bg-gray-900/50 flex items-center justify-center"
                                    x-show="uploading"
                                    x-cloak
                                >
                                    <x-icons.spinner class="w-6 h-6 text-white animate-spin" />
                                </div>
                            </div>
                        </div>

                        {{-- Upload Instructions & Actions --}}
                        <div class="flex-1 pt-1">
                            <p class="text-sm text-gray-600 mb-3">
                                {{ __('Click the avatar to upload a new photo. JPG, PNG or GIF. Max 5MB.') }}
                            </p>

                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    x-on:click="$refs.imageInput.click()"
                                    :disabled="uploading"
                                    class="text-sm font-medium text-coral-600 hover:text-coral-700 disabled:opacity-50"
                                >
                                    {{ __('Upload photo') }}
                                </button>

                                <template x-if="hasImage">
                                    <button
                                        type="button"
                                        x-on:click="deleteImage()"
                                        :disabled="deleting"
                                        class="text-sm font-medium text-gray-500 hover:text-red-600 disabled:opacity-50 transition-colors"
                                    >
                                        <span x-show="!deleting">{{ __('Remove') }}</span>
                                        <span x-show="deleting" class="flex items-center gap-1">
                                            <x-icons.spinner class="w-3 h-3 animate-spin" />
                                            {{ __('Removing...') }}
                                        </span>
                                    </button>
                                </template>
                            </div>

                            {{-- Error Message --}}
                            <p x-show="error" x-text="error" class="form-error mt-2" x-cloak></p>
                        </div>
                    </div>
                </div>

                {{-- Profile Form --}}
                <form method="POST" action="{{ route('settings.profile.update', ['locale' => app()->getLocale()]) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="name" class="form-label">
                            {{ __('Name') }}
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', auth()->user()->name) }}"
                            required
                            class="form-input @error('name') border-red-500 @enderror"
                        >
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="email" class="form-label">
                            {{ __('Email') }}
                        </label>
                        <input
                            type="email"
                            id="email"
                            value="{{ auth()->user()->email }}"
                            disabled
                            class="form-input bg-gray-50 text-gray-500 cursor-not-allowed"
                        >
                        <p class="form-help">{{ __('Email address cannot be changed at this time.') }}</p>
                    </div>

                    <div class="mb-6">
                        <label for="locale" class="form-label">
                            {{ __('Language') }}
                        </label>
                        <select
                            id="locale"
                            name="locale"
                            class="form-select @error('locale') border-red-500 @enderror"
                        >
                            @foreach(\App\Enums\SupportedLocale::cases() as $locale)
                                <option value="{{ $locale->value }}" {{ auth()->user()->locale_preference === $locale->value ? 'selected' : '' }}>
                                    {{ $locale->flag() }} {{ $locale->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('locale')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-primary">
                            <x-icons.checkmark class="w-5 h-5" />
                            {{ __('Save') }}
                        </button>

                        @if(session('status') === 'profile-updated')
                            <p class="text-sm text-teal-600 flex items-center gap-1.5">
                                <x-icons.check-circle class="w-4 h-4" />
                                {{ __('Saved.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-200"></div>

        {{-- Update Password --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Update password') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
            </div>

            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('settings.password.update', ['locale' => app()->getLocale()]) }}">
                    @csrf
                    @method('PUT')

                    @if(auth()->user()->password)
                        <div class="mb-6">
                            <label for="current_password" class="form-label">
                                {{ __('Current password') }}
                            </label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                autocomplete="current-password"
                                class="form-input @error('current_password') border-red-500 @enderror"
                            >
                            @error('current_password')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div class="info-box-success mb-6">
                            <div class="flex gap-3">
                                <x-icons.info-circle class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5" />
                                <div class="text-sm">
                                    <p class="font-medium text-teal-800">{{ __('No password set') }}</p>
                                    <p class="text-teal-700">{{ __('You signed up using a social login. Set a password to enable email login.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-6">
                        <label for="password" class="form-label">
                            {{ __('New password') }}
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            class="form-input @error('password') border-red-500 @enderror"
                        >
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="form-label">
                            {{ __('Confirm password') }}
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            class="form-input"
                        >
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-primary">
                            <x-icons.checkmark class="w-5 h-5" />
                            {{ __('Save') }}
                        </button>

                        @if(session('status') === 'password-updated')
                            <p class="text-sm text-teal-600 flex items-center gap-1.5">
                                <x-icons.check-circle class="w-4 h-4" />
                                {{ __('Saved.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-200"></div>

        {{-- Notifications --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{ friendNotifications: @js(auth()->user()->friend_notifications_enabled), saving: false }">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Notifications') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('Manage your email notification preferences.') }}</p>
            </div>

            <div class="lg:col-span-2">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-cream-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-coral-100 flex items-center justify-center">
                                <x-icons.users class="w-5 h-5 text-coral-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ __('Friends\' wishlist updates') }}</p>
                                <p class="text-sm text-gray-500">{{ __('Receive daily updates when friends update their wishlists') }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            x-on:click="async () => {
                                saving = true;
                                try {
                                    const response = await fetch('{{ route('friends.notifications.global', ['locale' => app()->getLocale()]) }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    });
                                    const data = await response.json();
                                    if (response.ok) {
                                        friendNotifications = data.enabled;
                                    }
                                } finally {
                                    saving = false;
                                }
                            }"
                            :disabled="saving"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-coral-500 focus:ring-offset-2 disabled:opacity-50"
                            :class="friendNotifications ? 'bg-coral-500' : 'bg-gray-200'"
                            role="switch"
                            :aria-checked="friendNotifications"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                :class="friendNotifications ? 'translate-x-5' : 'translate-x-0'"
                            ></span>
                        </button>
                    </div>

                    <p class="text-sm text-gray-500">
                        {{ __('You can also manage notifications for individual wishlists from the') }}
                        <a href="{{ route('friends.index', ['locale' => app()->getLocale()]) }}" class="text-coral-600 hover:text-coral-700 font-medium">{{ __('Friends page') }}</a>.
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200"></div>

        {{-- Two-Factor Authentication --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="twoFactorAuth()">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Two-factor authentication') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('Add additional security to your account using two-factor authentication.') }}</p>
            </div>

            <div class="lg:col-span-2">
                {{-- 2FA Not Enabled --}}
                @if (! auth()->user()->hasEnabledTwoFactorAuthentication())
                    <template x-if="!enabling && !showPasswordConfirm">
                        <div>
                            <div class="flex items-start gap-4 p-4 bg-cream-50 rounded-xl mb-6">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <x-icons.shield-check class="w-5 h-5 text-gray-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ __('You have not enabled two-factor authentication.') }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ __('When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s authenticator application.') }}</p>
                                </div>
                            </div>

                            <button
                                type="button"
                                x-on:click="enableTwoFactor()"
                                :disabled="loading"
                                class="inline-flex items-center gap-2 bg-teal-600 text-white px-5 py-2.5 rounded-xl hover:bg-teal-700 transition-colors font-medium disabled:opacity-50"
                            >
                                <x-icons.shield-check class="w-5 h-5" />
                                <span x-show="!loading">{{ __('Enable two-factor authentication') }}</span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <x-icons.spinner class="w-4 h-4 animate-spin" />
                                    {{ __('Enabling...') }}
                                </span>
                            </button>
                        </div>
                    </template>

                    {{-- Password Confirmation --}}
                    <template x-if="showPasswordConfirm">
                        <div class="space-y-6">
                            <div class="info-box">
                                <div class="flex gap-3">
                                    <x-icons.lock class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" />
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-800">{{ __('Confirm your password') }}</p>
                                        <p class="text-gray-600">{{ __('For your security, please confirm your password before enabling two-factor authentication.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <form x-on:submit.prevent="confirmPassword()" class="space-y-4">
                                <div>
                                    <label for="2fa_password" class="form-label">
                                        {{ __('Password') }}
                                    </label>
                                    <input
                                        type="password"
                                        id="2fa_password"
                                        x-model="password"
                                        autocomplete="current-password"
                                        required
                                        class="form-input max-w-sm"
                                        :class="{ 'border-red-500': passwordError }"
                                    >
                                    <p x-show="passwordError" x-text="passwordError" class="form-error" x-cloak></p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button
                                        type="submit"
                                        :disabled="passwordLoading || !password"
                                        class="inline-flex items-center gap-2 bg-teal-600 text-white px-5 py-2.5 rounded-xl hover:bg-teal-700 transition-colors font-medium disabled:opacity-50"
                                    >
                                        <span x-show="!passwordLoading">{{ __('Continue') }}</span>
                                        <span x-show="passwordLoading" class="flex items-center gap-2">
                                            <x-icons.spinner class="w-4 h-4 animate-spin" />
                                            {{ __('Verifying...') }}
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="cancelPasswordConfirm()"
                                        class="px-4 py-2.5 text-gray-600 hover:text-gray-800 font-medium"
                                    >
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </template>

                    {{-- Setup Flow --}}
                    <template x-if="enabling">
                        <div class="space-y-6">
                            <div class="info-box-success">
                                <div class="flex gap-3">
                                    <x-icons.info-circle class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5" />
                                    <div class="text-sm">
                                        <p class="font-medium text-teal-800">{{ __('Finish enabling two-factor authentication') }}</p>
                                        <p class="text-teal-700">{{ __('Scan the QR code below with your authenticator app (like Google Authenticator or Authy), then enter the verification code.') }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- QR Code Display --}}
                            <div class="flex flex-col sm:flex-row gap-6 items-start">
                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                                    <div x-html="qrCode" class="w-40 h-40 flex items-center justify-center">
                                        <x-icons.spinner class="w-8 h-8 animate-spin text-gray-400" />
                                    </div>
                                </div>

                                <div class="flex-1 space-y-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Or enter this code manually:') }}</p>
                                        <div class="flex items-center gap-2">
                                            <code
                                                x-text="secretKey || '{{ __('Loading...') }}'"
                                                class="bg-gray-100 px-3 py-2 rounded-lg font-mono text-sm text-gray-800 tracking-wider select-all"
                                            ></code>
                                            <button
                                                type="button"
                                                x-on:click="copySecret()"
                                                x-show="secretKey"
                                                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                                :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"
                                            >
                                                <x-icons.copy class="w-4 h-4" x-show="!copied" />
                                                <x-icons.check-circle class="w-4 h-4 text-teal-600" x-show="copied" x-cloak />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Confirmation Form --}}
                            <form x-on:submit.prevent="confirmTwoFactor()" class="space-y-4">
                                <div>
                                    <label for="code" class="form-label">
                                        {{ __('Verification code') }}
                                    </label>
                                    <input
                                        type="text"
                                        id="code"
                                        x-model="confirmationCode"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        autocomplete="one-time-code"
                                        maxlength="6"
                                        required
                                        class="form-input max-w-xs font-mono text-lg tracking-[0.5em] text-center"
                                        :class="{ 'border-red-500': confirmError }"
                                        placeholder="000000"
                                    >
                                    <p x-show="confirmError" x-text="confirmError" class="form-error" x-cloak></p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button
                                        type="submit"
                                        :disabled="confirmLoading || confirmationCode.length < 6"
                                        class="inline-flex items-center gap-2 bg-teal-600 text-white px-5 py-2.5 rounded-xl hover:bg-teal-700 transition-colors font-medium disabled:opacity-50"
                                    >
                                        <span x-show="!confirmLoading">{{ __('Confirm') }}</span>
                                        <span x-show="confirmLoading" class="flex items-center gap-2">
                                            <x-icons.spinner class="w-4 h-4 animate-spin" />
                                            {{ __('Confirming...') }}
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="cancelSetup()"
                                        class="px-4 py-2.5 text-gray-600 hover:text-gray-800 font-medium"
                                    >
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </template>
                @else
                    {{-- 2FA Enabled --}}
                    <div>
                        @if (auth()->user()->two_factor_confirmed_at)
                            {{-- Confirmed --}}
                            <div class="flex items-start gap-4 p-4 bg-teal-50 rounded-xl border border-teal-100 mb-6">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                                    <x-icons.shield-check class="w-5 h-5 text-teal-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-teal-900">{{ __('Two-factor authentication is enabled.') }}</p>
                                    <p class="text-sm text-teal-700 mt-1">{{ __('Your account is protected with an additional layer of security.') }}</p>
                                </div>
                            </div>

                            {{-- Recovery Codes --}}
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-medium text-gray-900">{{ __('Recovery codes') }}</h3>
                                    <button
                                        type="button"
                                        x-on:click="showRecoveryCodes = !showRecoveryCodes"
                                        class="text-sm text-coral-600 hover:text-coral-700 font-medium"
                                    >
                                        <span x-show="!showRecoveryCodes">{{ __('Show codes') }}</span>
                                        <span x-show="showRecoveryCodes">{{ __('Hide codes') }}</span>
                                    </button>
                                </div>

                                <p class="text-sm text-gray-600 mb-4">{{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.') }}</p>

                                <div x-show="showRecoveryCodes" x-collapse x-cloak>
                                    <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                        <div x-show="!recoveryCodes" class="flex items-center justify-center py-4">
                                            <x-icons.spinner class="w-6 h-6 animate-spin text-gray-400" />
                                        </div>
                                        <div x-show="recoveryCodes" class="grid grid-cols-2 gap-2">
                                            <template x-for="code in recoveryCodes" :key="code">
                                                <code class="bg-white px-3 py-2 rounded-lg font-mono text-sm text-gray-700 text-center border border-gray-200" x-text="code"></code>
                                            </template>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="text-sm text-gray-600 hover:text-gray-800 font-medium underline"
                                        >
                                            {{ __('Regenerate recovery codes') }}
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Disable 2FA --}}
                            <form method="POST" action="{{ route('two-factor.disable') }}" x-data>
                                @csrf
                                @method('DELETE')
                                <button
                                    type="button"
                                    x-on:click="$dispatch('open-confirm-disable-2fa')"
                                    class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl hover:bg-gray-200 transition-colors font-medium"
                                >
                                    {{ __('Disable two-factor authentication') }}
                                </button>
                            </form>
                        @else
                            {{-- Enabled but not confirmed (shouldn't happen normally) --}}
                            <div class="info-box-warning mb-6">
                                <div class="flex gap-3">
                                    <x-icons.warning class="w-5 h-5 text-sunny-600 flex-shrink-0 mt-0.5" />
                                    <div class="text-sm">
                                        <p class="font-medium text-sunny-800">{{ __('Two-factor setup incomplete') }}</p>
                                        <p class="text-sunny-700">{{ __('Please finish setting up two-factor authentication.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="border-t border-gray-200"></div>

        {{-- Browser Sessions --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Browser sessions') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('Manage and log out your active sessions on other browsers and devices.') }}</p>
            </div>

            <div class="lg:col-span-2">
                <p class="text-sm text-gray-600 mb-6">{{ __('If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.') }}</p>

                @if(session('status') === 'session-deleted')
                    <div class="alert-success mb-6">
                        <div class="flex items-center gap-2">
                            <x-icons.check-circle class="w-5 h-5" />
                            {{ __('Session has been logged out.') }}
                        </div>
                    </div>
                @endif

                @if(session('status') === 'all-sessions-deleted')
                    <div class="alert-success mb-6">
                        <div class="flex items-center gap-2">
                            <x-icons.check-circle class="w-5 h-5" />
                            {{ __('All other sessions have been logged out.') }}
                        </div>
                    </div>
                @endif

                <div class="space-y-4 mb-6">
                    @foreach($sessions as $session)
                        <div class="flex items-center justify-between p-4 bg-cream-50 rounded-xl">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white flex items-center justify-center border border-cream-200">
                                    @if($session->device === 'Mobile' || $session->device === 'iPhone')
                                        <x-icons.device-mobile class="w-5 h-5 text-gray-500" />
                                    @elseif($session->device === 'Tablet' || $session->device === 'iPad')
                                        <x-icons.device-tablet class="w-5 h-5 text-gray-500" />
                                    @else
                                        <x-icons.device-desktop class="w-5 h-5 text-gray-500" />
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900">
                                            {{ $session->platform }} - {{ $session->browser }}
                                        </span>
                                        @if($session->is_current)
                                            <span class="badge badge-success text-xs">{{ __('This device') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        {{ $session->ip_address }} &middot; {{ $session->last_active }}
                                    </p>
                                </div>
                            </div>

                            @unless($session->is_current)
                                <form method="POST" action="{{ route('settings.sessions.destroy', ['locale' => app()->getLocale(), 'session' => $session->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-gray-500 hover:text-red-600 transition-colors font-medium">
                                        {{ __('Log out') }}
                                    </button>
                                </form>
                            @endunless
                        </div>
                    @endforeach
                </div>

                @if(count($sessions) > 1)
                    <form method="POST" action="{{ route('settings.sessions.destroy-all', ['locale' => app()->getLocale()]) }}" x-data>
                        @csrf
                        @method('DELETE')
                        <button
                            type="button"
                            x-on:click="$dispatch('open-confirm-logout-sessions')"
                            class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl hover:bg-gray-200 transition-colors font-medium"
                        >
                            <x-icons.logout class="w-5 h-5" />
                            {{ __('Log out other browser sessions') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>
</x-app-content>

{{-- Danger Zone - Outside main card like gift/list edit pages --}}
<div class="mt-8 bg-white/60 backdrop-blur-sm rounded-2xl border border-red-200/60 p-6" x-data>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-red-600">{{ __('Danger zone') }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ __('Once you delete your account, all of its resources and data will be permanently deleted. This action cannot be undone.') }}</p>
        </div>
        <button
            type="button"
            x-on:click="$dispatch('open-confirm-delete-account')"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-5 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium whitespace-nowrap"
        >
            <x-icons.trash class="w-5 h-5" />
            {{ __('Delete account') }}
        </button>
    </div>
</div>

{{-- Confirm Delete Account Modal --}}
<x-confirm-modal
    id="delete-account"
    :title="__('Delete account')"
    :message="__('Are you sure you want to delete your account? All of your data will be permanently removed. This action cannot be undone.')"
    :confirmText="__('Delete account')"
    :customButtons="true"
>
    <form method="POST" action="{{ route('settings.account.destroy', ['locale' => app()->getLocale()]) }}">
        @csrf
        @method('DELETE')

        @if(auth()->user()->password)
            <div class="mb-6">
                <label for="delete_password" class="form-label">
                    {{ __('Enter your password to confirm') }}
                </label>
                <input
                    type="password"
                    id="delete_password"
                    name="password"
                    required
                    class="form-input @error('password') border-red-500 @enderror"
                    placeholder="{{ __('Password') }}"
                >
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div class="mb-6">
                <label for="delete_email" class="form-label">
                    {{ __('Enter your email to confirm') }}
                </label>
                <input
                    type="email"
                    id="delete_email"
                    name="email_confirmation"
                    required
                    class="form-input @error('email_confirmation') border-red-500 @enderror"
                    placeholder="{{ auth()->user()->email }}"
                >
                @error('email_confirmation')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div class="flex items-center justify-end gap-3">
            <button
                type="button"
                x-on:click="open = false"
                class="px-4 py-2.5 text-gray-700 font-medium hover:bg-gray-100 rounded-xl transition-colors"
            >
                {{ __('Cancel') }}
            </button>
            <button
                type="submit"
                class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium"
            >
                <x-icons.trash class="w-5 h-5" />
                {{ __('Delete account') }}
            </button>
        </div>
    </form>
</x-confirm-modal>

{{-- Confirm Logout All Sessions Modal --}}
<x-confirm-modal
    id="logout-sessions"
    :title="__('Log out other sessions')"
    :message="__('Are you sure you want to log out of all other browser sessions?')"
    :confirmText="__('Log out sessions')"
    :destructive="false"
>
    <form method="POST" action="{{ route('settings.sessions.destroy-all', ['locale' => app()->getLocale()]) }}">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center gap-2 bg-coral-500 text-white px-4 py-2.5 rounded-xl hover:bg-coral-600 transition-colors font-medium"
        >
            <x-icons.logout class="w-5 h-5" />
            {{ __('Log out sessions') }}
        </button>
    </form>
</x-confirm-modal>

{{-- Confirm Disable 2FA Modal --}}
<x-confirm-modal
    id="disable-2fa"
    :title="__('Disable two-factor authentication')"
    :message="__('Are you sure you want to disable two-factor authentication? Your account will be less secure.')"
    :confirmText="__('Disable')"
>
    <form method="POST" action="{{ route('two-factor.disable') }}">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-xl hover:bg-red-700 transition-colors font-medium"
        >
            {{ __('Disable two-factor authentication') }}
        </button>
    </form>
</x-confirm-modal>
@endsection

@push('scripts')
<script>
function profileSettings() {
    return {
        imageUrl: @js(auth()->user()->getProfileImageUrl('medium')),
        hasImage: @js(auth()->user()->hasProfileImage()),
        uploading: false,
        deleting: false,
        error: null,

        async uploadImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.uploading = true;
            this.error = null;

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('{{ route('settings.profile.image.upload', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    this.error = data.message || '{{ __('Failed to upload image.') }}';
                } else {
                    this.imageUrl = data.image_url;
                    this.hasImage = true;
                }
            } catch (error) {
                console.error('Upload error:', error);
                this.error = '{{ __('Failed to upload image.') }}';
            } finally {
                this.uploading = false;
                event.target.value = '';
            }
        },

        async deleteImage() {
            this.deleting = true;
            this.error = null;

            try {
                const response = await fetch('{{ route('settings.profile.image.delete', ['locale' => app()->getLocale()]) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    this.error = data.message || '{{ __('Failed to delete image.') }}';
                } else {
                    this.imageUrl = null;
                    this.hasImage = false;
                }
            } catch (error) {
                console.error('Delete error:', error);
                this.error = '{{ __('Failed to delete image.') }}';
            } finally {
                this.deleting = false;
            }
        }
    }
}

function twoFactorAuth() {
    return {
        enabling: false,
        loading: false,
        qrCode: null,
        secretKey: null,
        confirmationCode: '',
        confirmError: null,
        confirmLoading: false,
        copied: false,
        showRecoveryCodes: false,
        recoveryCodes: null,
        // Password confirmation
        showPasswordConfirm: false,
        password: '',
        passwordError: null,
        passwordLoading: false,

        async enableTwoFactor() {
            this.loading = true;
            try {
                const response = await fetch('{{ route('two-factor.enable') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.enabling = true;
                    await this.fetchQrCode();
                    await this.fetchSecretKey();
                } else if (response.status === 423) {
                    // Password confirmation required
                    this.showPasswordConfirm = true;
                    this.password = '';
                    this.passwordError = null;
                }
            } catch (error) {
                console.error('Error enabling 2FA:', error);
            } finally {
                this.loading = false;
            }
        },

        async confirmPassword() {
            this.passwordError = null;
            this.passwordLoading = true;

            try {
                const response = await fetch('{{ route('password.confirm.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ password: this.password })
                });

                if (response.ok) {
                    // Password confirmed, now enable 2FA
                    this.showPasswordConfirm = false;
                    this.password = '';
                    await this.enableTwoFactor();
                } else {
                    const data = await response.json();
                    this.passwordError = data.message || '{{ __('The provided password was incorrect.') }}';
                }
            } catch (error) {
                this.passwordError = '{{ __('An error occurred. Please try again.') }}';
            } finally {
                this.passwordLoading = false;
            }
        },

        cancelPasswordConfirm() {
            this.showPasswordConfirm = false;
            this.password = '';
            this.passwordError = null;
        },

        async fetchQrCode() {
            try {
                const response = await fetch('{{ route('two-factor.qr-code') }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.qrCode = data.svg;
            } catch (error) {
                console.error('Error fetching QR code:', error);
            }
        },

        async fetchSecretKey() {
            try {
                const response = await fetch('{{ route('two-factor.secret-key') }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.secretKey = data.secretKey;
            } catch (error) {
                console.error('Error fetching secret key:', error);
            }
        },

        async confirmTwoFactor() {
            this.confirmError = null;
            this.confirmLoading = true;

            try {
                const response = await fetch('{{ route('two-factor.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: this.confirmationCode })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    this.confirmError = data.message || '{{ __('The provided two-factor authentication code was invalid.') }}';
                }
            } catch (error) {
                this.confirmError = '{{ __('An error occurred. Please try again.') }}';
            } finally {
                this.confirmLoading = false;
            }
        },

        async cancelSetup() {
            try {
                await fetch('{{ route('two-factor.disable') }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                this.enabling = false;
                this.qrCode = null;
                this.secretKey = null;
                this.confirmationCode = '';
            } catch (error) {
                console.error('Error cancelling setup:', error);
            }
        },

        copySecret() {
            navigator.clipboard.writeText(this.secretKey);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },

        async init() {
            if (this.showRecoveryCodes) {
                await this.fetchRecoveryCodes();
            }

            this.$watch('showRecoveryCodes', async (value) => {
                if (value && !this.recoveryCodes) {
                    await this.fetchRecoveryCodes();
                }
            });
        },

        async fetchRecoveryCodes() {
            try {
                const response = await fetch('{{ route('two-factor.recovery-codes') }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                this.recoveryCodes = await response.json();
            } catch (error) {
                console.error('Error fetching recovery codes:', error);
            }
        }
    }
}
</script>
@endpush
