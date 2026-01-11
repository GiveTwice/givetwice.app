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
}
