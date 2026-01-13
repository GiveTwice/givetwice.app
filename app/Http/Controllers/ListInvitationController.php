<?php

namespace App\Http\Controllers;

use App\Actions\AcceptListInvitationAction;
use App\Actions\DeclineListInvitationAction;
use App\Actions\InviteToListAction;
use App\Exceptions\ListInvitation\ListInvitationException;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListInvitationController extends Controller
{
    public function create(string $locale, GiftList $list): View
    {
        $this->authorize('invite', $list);

        $list->load(['users:id,name,email,avatar', 'pendingInvitations.inviter:id,name']);

        return view('lists.invite', [
            'list' => $list,
        ]);
    }

    public function store(Request $request, string $locale, GiftList $list): RedirectResponse
    {
        $this->authorize('invite', $list);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $action = new InviteToListAction;
            $action->execute($list, $request->user(), $validated['email']);

            return redirect()
                ->back()
                ->with('success', __('Invitation sent to :email.', ['email' => $validated['email']]));
        } catch (ListInvitationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, string $locale, string $token): View|RedirectResponse
    {
        $invitation = ListInvitation::where('token', $token)
            ->with(['list', 'inviter'])
            ->first();

        $validationError = $this->validateInvitationAccess($invitation, $request->user());

        if ($validationError) {
            return redirect()
                ->route('dashboard.locale', ['locale' => $locale])
                ->with($validationError['type'], $validationError['message']);
        }

        return view('lists.invitation-show', [
            'invitation' => $invitation,
            'list' => $invitation->list,
            'inviter' => $invitation->inviter,
        ]);
    }

    public function accept(Request $request, string $locale, string $token): RedirectResponse
    {
        try {
            $action = new AcceptListInvitationAction;
            $list = $action->execute($token, $request->user());

            return redirect()
                ->route('dashboard.locale', ['locale' => $locale])
                ->with('success', __('You now have access to ":name".', ['name' => $list->name]));
        } catch (ListInvitationException $e) {
            return redirect()
                ->route('dashboard.locale', ['locale' => $locale])
                ->with('error', $e->getMessage());
        }
    }

    public function decline(Request $request, string $locale, string $token): RedirectResponse
    {
        try {
            $action = new DeclineListInvitationAction;
            $action->execute($token, $request->user());

            return redirect()
                ->route('dashboard.locale', ['locale' => $locale]);
        } catch (ListInvitationException $e) {
            return redirect()
                ->route('dashboard.locale', ['locale' => $locale])
                ->with('error', $e->getMessage());
        }
    }

    public function leave(Request $request, string $locale, GiftList $list): RedirectResponse
    {
        $this->authorize('leave', $list);

        if ($list->users()->count() <= 1) {
            return redirect()
                ->back()
                ->with('error', __('You cannot leave as the only collaborator.'));
        }

        if ($list->is_default && $list->creator_id === $request->user()->id) {
            return redirect()
                ->back()
                ->with('error', __('You cannot leave your default list.'));
        }

        $list->users()->detach($request->user()->id);

        return redirect()
            ->route('dashboard.locale', ['locale' => $locale])
            ->with('success', __('You have left ":name".', ['name' => $list->name]));
    }

    public function removeCollaborator(Request $request, string $locale, GiftList $list, User $user): RedirectResponse
    {
        $this->authorize('update', $list);

        if ($user->id === $request->user()->id) {
            return redirect()
                ->back()
                ->with('error', __('You cannot remove yourself. Use the leave option instead.'));
        }

        if ($user->id === $list->creator_id) {
            return redirect()
                ->back()
                ->with('error', __('Cannot remove the list creator.'));
        }

        if ($list->users()->count() <= 1) {
            return redirect()
                ->back()
                ->with('error', __('Cannot remove the last collaborator.'));
        }

        $list->users()->detach($user->id);

        return redirect()
            ->back()
            ->with('success', __('Removed :name from the list.', ['name' => $user->name]));
    }

    public function cancelInvitation(Request $request, string $locale, ListInvitation $invitation): RedirectResponse
    {
        $this->authorize('update', $invitation->list);

        $invitation->delete();

        return redirect()
            ->back()
            ->with('success', __('Invitation cancelled.'));
    }

    /**
     * @return array{type: string, message: string}|null
     */
    private function validateInvitationAccess(?ListInvitation $invitation, User $user): ?array
    {
        if (! $invitation) {
            return ['type' => 'error', 'message' => __('This invitation link is invalid.')];
        }

        if ($invitation->accepted_at) {
            return ['type' => 'success', 'message' => __('This invitation has already been accepted.')];
        }

        if ($invitation->declined_at) {
            return ['type' => 'success', 'message' => __('This invitation has been declined.')];
        }

        if ($invitation->isExpired()) {
            return ['type' => 'error', 'message' => __('This invitation has expired.')];
        }

        if ($invitation->invitee_id && $invitation->invitee_id !== $user->id) {
            return ['type' => 'error', 'message' => __('This invitation was sent to a different account.')];
        }

        if ($invitation->email !== $user->email) {
            return ['type' => 'error', 'message' => __('This invitation was sent to a different email address.')];
        }

        return null;
    }
}
