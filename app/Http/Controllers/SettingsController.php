<?php

namespace App\Http\Controllers;

use App\Actions\DeleteAccountAction;
use App\Actions\DeleteUserProfileImageAction;
use App\Actions\ExportPersonalDataAction;
use App\Actions\UpdatePasswordAction;
use App\Actions\UploadUserProfileImageAction;
use App\Enums\SupportedLocale;
use App\Events\ProfileImageUpdated;
use App\Rules\MatchesUserEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class SettingsController extends Controller
{
    public function show(Request $request): View
    {
        $sessions = $this->getSessionsForUser($request);

        return view('settings', [
            'sessions' => $sessions,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'in:'.implode(',', SupportedLocale::values())],
        ]);

        $user = $request->user();
        $oldLocale = $user->locale_preference;
        $newLocale = $validated['locale'];

        $user->update([
            'name' => $validated['name'],
            'locale_preference' => $newLocale,
        ]);

        if ($oldLocale !== $newLocale) {
            return redirect()
                ->route('settings', ['locale' => $newLocale])
                ->with('status', 'profile-updated');
        }

        return back()->with('status', 'profile-updated');
    }

    public function uploadProfileImage(Request $request, UploadUserProfileImageAction $action): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ], [
            'image.max' => __('The image must not be larger than 5MB.'),
        ]);

        $user = $request->user();
        $action->execute($user, $validated['image']);

        $user->refresh();

        if (! $user->hasMedia('profile')) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to upload image.'),
            ], 422);
        }

        ProfileImageUpdated::dispatch($user);

        return response()->json([
            'success' => true,
            'message' => __('Profile image uploaded successfully.'),
            'image_url' => $user->getProfileImageUrl('medium'),
        ]);
    }

    public function deleteProfileImage(Request $request, DeleteUserProfileImageAction $action): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasProfileImage()) {
            return response()->json([
                'success' => false,
                'message' => __('No profile image to delete.'),
            ], 422);
        }

        $action->execute($user);

        ProfileImageUpdated::dispatch($user->fresh());

        return response()->json([
            'success' => true,
            'message' => __('Profile image deleted successfully.'),
        ]);
    }

    public function updatePassword(Request $request, UpdatePasswordAction $action): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'current_password' => $user->password ? ['required', 'string'] : ['nullable'],
        ]);

        $action->execute($user, $validated['password'], $validated['current_password'] ?? null);

        return back()->with('status', 'password-updated');
    }

    public function destroySession(Request $request, string $locale, string $session): RedirectResponse
    {
        $currentSessionId = $request->session()->getId();

        if ($session === $currentSessionId) {
            return back()->with('error', __('You cannot log out of your current session from here.'));
        }

        DB::table('sessions')
            ->where('id', $session)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('status', 'session-deleted');
    }

    public function destroyAllSessions(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->password) {
            $request->validate([
                'password' => ['required', 'string', 'current_password:web'],
            ], [
                'password.current_password' => __('The provided password does not match your current password.'),
            ]);
        }

        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return back()->with('status', 'all-sessions-deleted');
    }

    public function destroyAccount(Request $request, DeleteAccountAction $action): RedirectResponse
    {
        $user = $request->user();

        // OAuth users without password need email confirmation instead
        if ($user->password) {
            $request->validate([
                'password' => ['required', 'string', 'current_password:web'],
            ], [
                'password.current_password' => __('The provided password does not match your current password.'),
            ]);
        } else {
            $request->validate([
                'email_confirmation' => ['required', 'email', new MatchesUserEmail($user)],
            ]);
        }

        $action->execute($user, 'User-initiated account deletion');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home', ['locale' => app()->getLocale()])->with('status', 'account-deleted');
    }

    public function exportData(Request $request, ExportPersonalDataAction $action): Response
    {
        $user = $request->user();
        $data = $action->execute($user);

        $filename = 'givetwice-data-export-'.now()->format('Y-m-d').'.json';
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function getSessionsForUser(Request $request): array
    {
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $agent = $this->parseUserAgent($session->user_agent ?? '');

                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'is_current' => $session->id === $currentSessionId,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'device' => $agent['device'],
                    'platform' => $agent['platform'],
                    'browser' => $agent['browser'],
                ];
            })
            ->all();

        return $sessions;
    }

    protected function parseUserAgent(string $userAgent): array
    {
        $agent = new Agent;
        $agent->setUserAgent($userAgent);

        $device = match (true) {
            $agent->isTablet() => 'Tablet',
            $agent->isMobile() => 'Mobile',
            default => 'Desktop',
        };

        return [
            'platform' => $agent->platform() ?: 'Unknown',
            'browser' => $agent->browser() ?: 'Unknown',
            'device' => $device,
        ];
    }
}
