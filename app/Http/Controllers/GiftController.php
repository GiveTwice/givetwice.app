<?php

namespace App\Http\Controllers;

use App\Actions\FetchGiftDetailsAction;
use App\Enums\SupportedCurrency;
use App\Enums\SupportedLocale;
use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GiftController extends Controller
{
    public function create(Request $request): View
    {
        $lists = $request->user()->lists()->get();
        $defaultList = $lists->firstWhere('is_default', true) ?? $lists->first();

        $selectedListId = $request->query('list');
        if ($selectedListId && $lists->contains('id', $selectedListId)) {
            $selectedListId = (int) $selectedListId;
        } else {
            $selectedListId = $defaultList?->id;
        }

        $locale = SupportedLocale::tryFrom(app()->getLocale()) ?? SupportedLocale::default();
        $defaultCurrency = $locale->defaultCurrency()->value;

        return view('gifts.create', [
            'lists' => $lists,
            'defaultList' => $defaultList,
            'selectedListId' => $selectedListId,
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
            'currency' => ['nullable', 'string', Rule::enum(SupportedCurrency::class)],
            'list_id' => ['nullable', Rule::exists('lists', 'id')->where('user_id', $request->user()->id)],
        ]);

        $priceInCents = isset($validated['price'])
            ? (int) round($validated['price'] * 100)
            : null;

        $gift = Gift::create([
            'user_id' => $request->user()->id,
            'url' => $validated['url'],
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'price_in_cents' => $priceInCents,
            'currency' => $validated['currency'] ?? SupportedCurrency::default()->value,
        ]);

        $list = ! empty($validated['list_id'])
            ? GiftList::find($validated['list_id'])
            : $request->user()->lists()->where('is_default', true)->first();

        $attachedListId = null;
        if ($list) {
            $list->gifts()->attach($gift->id, [
                'sort_order' => $list->gifts()->count(),
                'added_at' => now(),
            ]);
            $attachedListId = $list->id;
        }

        $locale = app()->getLocale();
        $anchor = $attachedListId ? "#list-{$attachedListId}" : '';

        return redirect()
            ->to("/{$locale}/dashboard{$anchor}")
            ->with('success', __('Gift added successfully! We\'re fetching the details.'));
    }

    public function edit(string $locale, Gift $gift): View
    {
        $this->authorize('update', $gift);

        return view('gifts.edit', [
            'gift' => $gift,
        ]);
    }

    public function update(Request $request, string $locale, Gift $gift): RedirectResponse
    {
        $this->authorize('update', $gift);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['nullable', 'string', Rule::enum(SupportedCurrency::class)],
            'url' => ['nullable', 'url', 'max:2048'],
        ]);

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

    public function destroy(string $locale, Gift $gift): RedirectResponse
    {
        $this->authorize('delete', $gift);

        $gift->delete();

        return redirect()
            ->to("/{$locale}/dashboard")
            ->with('success', __('Gift deleted successfully.'));
    }

    public function refreshGiftDetails(Request $request, string $locale, Gift $gift): RedirectResponse|JsonResponse
    {
        $gift->update(['fetch_status' => 'pending']);

        FetchGiftDetailsAction::dispatch($gift);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Gift details refresh has been queued.'),
                'fetch_status' => 'pending',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', __('Gift details refresh has been queued.'));
    }
}
