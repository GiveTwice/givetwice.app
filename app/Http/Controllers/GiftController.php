<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftController extends Controller
{
    public function create(Request $request): View
    {
        $lists = $request->user()->lists()->get();
        $defaultList = $lists->firstWhere('is_default', true) ?? $lists->first();

        $defaultCurrency = match (app()->getLocale()) {
            'en' => 'USD',
            default => 'EUR',
        };

        return view('gifts.create', [
            'lists' => $lists,
            'defaultList' => $defaultList,
            'isSingleListMode' => $lists->count() === 1,
            'defaultCurrency' => $defaultCurrency,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['nullable', 'string', 'in:EUR,USD'],
            'list_id' => ['nullable', 'exists:lists,id'],
        ]);

        // If list_id provided, verify ownership
        $list = null;
        if (! empty($validated['list_id'])) {
            $list = GiftList::where('id', $validated['list_id'])
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        // Convert price from decimal to cents
        $priceInCents = isset($validated['price'])
            ? (int) round($validated['price'] * 100)
            : null;

        $gift = Gift::create([
            'user_id' => $request->user()->id,
            'url' => $validated['url'],
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'price_in_cents' => $priceInCents,
            'currency' => $validated['currency'] ?? 'EUR',
        ]);

        // Attach to list if specified, otherwise attach to default list
        if ($list !== null) {
            $list->gifts()->attach($gift->id, [
                'sort_order' => $list->gifts()->count(),
                'added_at' => now(),
            ]);
        } else {
            /** @var GiftList|null $defaultList */
            $defaultList = $request->user()->lists()->where('is_default', true)->first();
            if ($defaultList) {
                $defaultList->gifts()->attach($gift->id, [
                    'sort_order' => $defaultList->gifts()->count(),
                    'added_at' => now(),
                ]);
            }
        }

        $locale = app()->getLocale();

        return redirect()
            ->to("/{$locale}/dashboard")
            ->with('success', __('Gift added successfully! We\'re fetching the details.'));
    }

    public function edit(Request $request, string $locale, Gift $gift): View
    {
        // Ensure user owns this gift
        if ($gift->user_id !== $request->user()->id) {
            abort(404);
        }

        return view('gifts.edit', [
            'gift' => $gift,
        ]);
    }

    public function update(Request $request, string $locale, Gift $gift): RedirectResponse
    {
        // Ensure user owns this gift
        if ($gift->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['nullable', 'string', 'in:EUR,USD'],
            'url' => ['nullable', 'url', 'max:2048'],
        ]);

        // Convert price from decimal to cents (only if provided)
        $priceInCents = isset($validated['price'])
            ? (int) round($validated['price'] * 100)
            : $gift->price_in_cents;

        $gift->update([
            'title' => $validated['title'] ?? $gift->title,
            'description' => $validated['description'] ?? $gift->description,
            'price_in_cents' => $priceInCents,
            'currency' => $validated['currency'] ?? $gift->currency,
            'url' => $validated['url'] ?? $gift->url,
        ]);

        return redirect()
            ->to("/{$locale}/dashboard")
            ->with('success', __('Gift updated successfully.'));
    }

    public function destroy(Request $request, string $locale, Gift $gift): RedirectResponse
    {
        // Ensure user owns this gift
        if ($gift->user_id !== $request->user()->id) {
            abort(404);
        }

        $gift->delete();

        return redirect()
            ->to("/{$locale}/dashboard")
            ->with('success', __('Gift deleted successfully.'));
    }
}
