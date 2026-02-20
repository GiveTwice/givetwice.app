<?php

namespace App\Http\Controllers;

use App\Actions\FetchGiftDetailsAction;
use App\Actions\ProcessGiftImageAction;
use App\Enums\SupportedCurrency;
use App\Enums\SupportedLocale;
use App\Events\GiftAddedToList;
use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GiftController extends Controller
{
    public function create(Request $request): View
    {
        $lists = $request->user()->lists()->get();
        /** @var GiftList|null $defaultList */
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
        /** @var GiftList|null $targetList */
        $targetList = $request->list_id
            ? $request->user()->lists()->find($request->list_id)
            : $request->user()->lists()->where('is_default', true)->first();

        if (! $targetList) {
            return back()
                ->withInput()
                ->withErrors(['list_id' => __('No valid list found. Please create a list first.')]);
        }

        $validated = $request->validate([
            'url' => [
                'required',
                'url',
                'max:2048',
                function ($attribute, $value, $fail) use ($targetList) {
                    if ($targetList->gifts()->where('url', $value)->exists()) {
                        $fail(__("You've already added this gift to this list."));
                    }
                },
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1500'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['nullable', 'string', Rule::enum(SupportedCurrency::class)],
            'list_id' => ['nullable', 'integer'],
            'allow_multiple_claims' => ['nullable', 'boolean'],
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
            'allow_multiple_claims' => $request->boolean('allow_multiple_claims'),
        ]);

        $targetList->gifts()->attach($gift->id, [
            'sort_order' => $targetList->gifts()->count(),
            'added_at' => now(),
        ]);

        GiftAddedToList::dispatch($gift, $targetList);

        $locale = app()->getLocale();
        $anchor = "#list-{$targetList->id}";

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

        $giftListIds = $gift->lists()->pluck('lists.id');

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1500'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['nullable', 'string', Rule::enum(SupportedCurrency::class)],
            'url' => [
                'nullable',
                'url',
                'max:2048',
                function ($attribute, $value, $fail) use ($gift, $giftListIds) {
                    if ($value === $gift->url) {
                        return;
                    }

                    $conflictExists = Gift::where('url', $value)
                        ->where('id', '!=', $gift->id)
                        ->whereHas('lists', fn ($q) => $q->whereIn('lists.id', $giftListIds))
                        ->exists();

                    if ($conflictExists) {
                        $fail(__("You've already added this gift to this list."));
                    }
                },
            ],
            'allow_multiple_claims' => ['nullable', 'boolean'],
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
            'allow_multiple_claims' => $request->boolean('allow_multiple_claims'),
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
        $this->authorize('update', $gift);

        $gift->update([
            'fetch_status' => 'pending',
            'fetch_error' => null,
            'fetch_attempts' => 0,
        ]);

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

    public function uploadImage(Request $request, string $locale, Gift $gift, ProcessGiftImageAction $imageAction): JsonResponse
    {
        $this->authorize('update', $gift);

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $success = $imageAction->fromUpload($gift, $validated['image']);

        if (! $success) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to upload image.'),
            ], 422);
        }

        $imageAction->dispatchCompletedEvent($gift);

        return response()->json([
            'success' => true,
            'message' => __('Image uploaded successfully.'),
        ]);
    }

    public function cardHtml(string $locale, int $list, string $slug, Gift $gift): Response
    {
        $list = GiftList::findOrFail($list);

        if ($list->slug !== $slug) {
            abort(404);
        }

        if (! $list->gifts()->where('gifts.id', $gift->id)->exists()) {
            abort(404);
        }

        $gift->loadCount('claims');

        $html = view('components.gift-card', [
            'gift' => $gift,
            'editable' => false,
            'showClaimActions' => true,
            'isOwner' => false,
            'openModal' => true,
        ])->render();

        return response($html)->header('Content-Type', 'text/html');
    }
}
