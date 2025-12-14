<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListController extends Controller
{
    public function show(Request $request, string $locale, GiftList $list): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user || $list->user_id !== $user->id) {
            return redirect()->route('public.list', ['locale' => $locale, 'list' => $list]);
        }

        return view('lists.show', [
            'list' => $list,
            'gifts' => $list->gifts()->paginate(100),
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
            ->route('list.show', ['locale' => app()->getLocale(), 'list' => $list])
            ->with('success', __('List created successfully.'));
    }

    public function edit(string $locale, GiftList $list): View
    {
        $this->authorize('update', $list);

        return view('lists.edit', [
            'list' => $list,
        ]);
    }

    public function update(Request $request, string $locale, GiftList $list): RedirectResponse
    {
        $this->authorize('update', $list);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $list->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('list.show', ['locale' => app()->getLocale(), 'list' => $list])
            ->with('success', __('List updated successfully.'));
    }

    public function destroy(string $locale, GiftList $list): RedirectResponse
    {
        $this->authorize('delete', $list);

        $list->gifts()->detach();
        $list->delete();

        return redirect()
            ->route('dashboard.locale', ['locale' => app()->getLocale()])
            ->with('success', __('List deleted successfully.'));
    }
}
