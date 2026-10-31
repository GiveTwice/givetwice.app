<?php

namespace App\Http\Controllers;

use App\Actions\CreateListAction;
use App\Http\Requests\UpdateListRequest;
use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListController extends Controller
{
    public function create(): View
    {
        return view('lists.create');
    }

    public function store(Request $request, CreateListAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $list = $action->execute(
            $request->user(),
            $validated['name'],
            $validated['description'] ?? null,
        );

        return redirect()
            ->route('dashboard.locale', ['locale' => app()->getLocale(), 'list-added' => $list->slug])
            ->with('success', __('List created successfully.'));
    }

    public function edit(string $locale, GiftList $list): View
    {
        $this->authorize('update', $list);

        return view('lists.edit', [
            'list' => $list,
        ]);
    }

    public function update(UpdateListRequest $request, string $locale, GiftList $list): RedirectResponse
    {
        $this->authorize('update', $list);

        $validated = $request->safe()->except('spoiler_confirmation');
        if ($request->boolean('spoiler_mode') && ! $list->spoiler_confirmed_at) {
            $validated['spoiler_confirmed_at'] = now();
        }
        $list->update($validated);

        return redirect()
            ->route('dashboard.locale', ['locale' => app()->getLocale()])
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
