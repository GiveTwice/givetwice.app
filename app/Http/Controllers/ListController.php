<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListController extends Controller
{
    public function show(Request $request, string $locale, string $slug): View|RedirectResponse
    {
        $list = GiftList::where('slug', $slug)->first();

        if (! $list) {
            abort(404);
        }

        $user = $request->user();

        // If user is not logged in or not the owner, redirect to public view
        if (! $user || $list->user_id !== $user->id) {
            return redirect()->route('public.list', ['locale' => $locale, 'slug' => $slug]);
        }

        $gifts = $list->gifts()->paginate(100);

        return view('lists.show', [
            'list' => $list,
            'gifts' => $gifts,
        ]);
    }

    public function create(): View
    {
        return view('lists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var GiftList $list */
        $list = $request->user()->lists()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => false,
        ]);

        return redirect()
            ->route('list.show', ['locale' => app()->getLocale(), 'slug' => $list->slug])
            ->with('success', __('List created successfully.'));
    }

    public function edit(Request $request, string $locale, string $slug): View
    {
        $list = GiftList::where('slug', $slug)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('lists.edit', [
            'list' => $list,
        ]);
    }

    public function update(Request $request, string $locale, string $slug): RedirectResponse
    {
        $list = GiftList::where('slug', $slug)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $list->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('list.show', ['locale' => app()->getLocale(), 'slug' => $list->slug])
            ->with('success', __('List updated successfully.'));
    }

    public function destroy(Request $request, string $locale, string $slug): RedirectResponse
    {
        $list = GiftList::where('slug', $slug)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($list->is_default) {
            return redirect()
                ->route('dashboard.locale', ['locale' => app()->getLocale()])
                ->with('error', __('Cannot delete your default list.'));
        }

        // Detach all gifts from this list (but don't delete the gifts)
        $list->gifts()->detach();
        $list->delete();

        return redirect()
            ->route('dashboard.locale', ['locale' => app()->getLocale()])
            ->with('success', __('List deleted successfully.'));
    }
}
