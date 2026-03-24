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

        return view('gifts.create', [
            'lists' => $lists,
            'defaultList' => $defaultList,
            'selectedListId' => $selectedListId,
            'isSingleListMode' => $lists->count() === 1,
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
            'input' => ['required', 'string', 'max:2048'],
            'list_id' => ['nullable', 'integer'],
        ]);

        $input = trim($validated['input']);

        if ($input === '') {
            return back()
                ->withInput()
                ->withErrors(['input' => __('validation.required', ['attribute' => 'input'])]);
        }

        $url = $this->looksLikeUrl($input);
        $locale = app()->getLocale();
        $localeCurrency = (SupportedLocale::tryFrom($locale) ?? SupportedLocale::default())
            ->defaultCurrency()->value;

        if ($url !== null) {
            // URL path — check for duplicates
            if ($targetList->gifts()->where('url', $url)->exists()) {
                return back()
                    ->withInput()
                    ->withErrors(['input' => __("You've already added this gift to this list.")]);
            }

            $gift = Gift::create([
                'user_id' => $request->user()->id,
                'url' => $url,
                'fetch_status' => 'pending',
                'currency' => $localeCurrency,
            ]);
        } else {
            // Text path — create with title, skip fetching
            if (mb_strlen($input) > 255) {
                return back()
                    ->withInput()
                    ->withErrors(['input' => __('validation.max.string', ['attribute' => 'input', 'max' => 255])]);
            }

            $gift = Gift::create([
                'user_id' => $request->user()->id,
                'url' => null,
                'title' => $input,
                'fetch_status' => 'skipped',
                'currency' => $localeCurrency,
            ]);
        }

        $targetList->gifts()->attach($gift->id, [
            'sort_order' => $targetList->gifts()->count(),
            'added_at' => now(),
        ]);

        GiftAddedToList::dispatch($gift, $targetList);

        if ($url !== null) {
            return redirect()->route('gifts.fetching', ['locale' => $locale, 'gift' => $gift]);
        }

        return redirect()
            ->route('gifts.edit', ['locale' => $locale, 'gift' => $gift])
            ->with('gift_context', 'manual_entry');
    }

    public function fetching(string $locale, Gift $gift): View|RedirectResponse
    {
        $this->authorize('update', $gift);

        if ($gift->isFetched()) {
            return redirect()
                ->route('gifts.edit', ['locale' => $locale, 'gift' => $gift])
                ->with('gift_context', 'fetch_success');
        }

        if ($gift->isFetchFailed()) {
            return redirect()
                ->route('gifts.edit', ['locale' => $locale, 'gift' => $gift])
                ->with('gift_context', 'fetch_failed');
        }

        if ($gift->isSkipped()) {
            return redirect()
                ->route('gifts.edit', ['locale' => $locale, 'gift' => $gift])
                ->with('gift_context', 'manual_entry');
        }

        return view('gifts.fetching', [
            'gift' => $gift,
        ]);
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

        $newUrl = $validated['url'] ?? $gift->url;
        $urlAdded = $gift->isSkipped() && $newUrl && $newUrl !== $gift->url;

        $updateData = [
            'title' => $validated['title'] ?? $gift->title,
            'description' => $validated['description'] ?? $gift->description,
            'price_in_cents' => $priceInCents,
            'currency' => $validated['currency'] ?? $gift->currency,
            'url' => $newUrl,
            'allow_multiple_claims' => $request->boolean('allow_multiple_claims'),
        ];

        if ($urlAdded) {
            $updateData['fetch_status'] = 'pending';
            $updateData['fetch_error'] = null;
            $updateData['fetch_attempts'] = 0;
        }

        $gift->update($updateData);

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

    public function cardHtml(string $locale, int $list, string $slug, int $giftId): Response
    {
        $giftList = GiftList::findOrFail($list);

        if ($giftList->slug !== $slug) {
            abort(404);
        }

        $gift = $giftList->gifts()->findOrFail($giftId);

        $gift->loadCount('claims');
        $gift->load(['claims' => fn ($q) => $q->whereNotNull('confirmed_at')]);

        $html = view('components.gift-card', [
            'gift' => $gift,
            'editable' => false,
            'showClaimActions' => true,
            'isOwner' => false,
            'openModal' => true,
        ])->render();

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Detect if user input looks like a URL. Returns normalized URL or null for text.
     */
    private function looksLikeUrl(string $input): ?string
    {
        if (str_contains($input, ' ')) {
            return null;
        }

        if (preg_match('#^https?://#i', $input) && filter_var($input, FILTER_VALIDATE_URL)) {
            return $input;
        }

        // Bare domain pattern: something.tld/... (no spaces, no scheme)
        if (preg_match('#^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z]{2,})+(/\S*)?$#i', $input)) {
            $withScheme = 'https://'.$input;
            if (filter_var($withScheme, FILTER_VALIDATE_URL)) {
                return $withScheme;
            }
        }

        return null;
    }
}
