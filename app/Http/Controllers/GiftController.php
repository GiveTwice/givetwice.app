<?php

namespace App\Http\Controllers;

use App\Actions\FetchGiftDetailsAction;
use App\Actions\ProcessGiftImageAction;
use App\Enums\SupportedLocale;
use App\Events\GiftAddedToList;
use App\Http\Requests\UpdateGiftRequest;
use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Spatie\OgImage\OgImage;

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

        $isFirstGift = ! Gift::withTrashed()->where('user_id', $request->user()->id)->exists();
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

        if ($isFirstGift) {
            $request->session()->put('first_gift', $gift->id);
        }

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

        if ($gift->created_at->diffInSeconds(now()) >= 30) {
            return redirect()->route('gifts.edit', ['locale' => $locale, 'gift' => $gift])
                ->with('gift_context', 'fetch_timeout');
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

    public function update(UpdateGiftRequest $request, string $locale, Gift $gift): RedirectResponse
    {
        $this->authorize('update', $gift);

        $validated = $request->validated();

        $priceInCents = array_key_exists('price', $validated)
            ? ($validated['price'] === null ? null : (int) round($validated['price'] * 100))
            : $gift->price_in_cents;

        $newUrl = $validated['url'] ?? $gift->url;
        $urlAdded = $gift->isSkipped() && $newUrl && $newUrl !== $gift->url;

        $updateData = [
            'most_wanted' => $validated['most_wanted'] ?? $gift->most_wanted,
            'title' => $validated['title'] ?? $gift->title,
            'owner_note' => array_key_exists('owner_note', $validated) ? $validated['owner_note'] : $gift->owner_note,
            'shop_name' => $validated['shop_name'] ?? $gift->shop_name,
            'description' => array_key_exists('description', $validated) ? $validated['description'] : $gift->description,
            'price_in_cents' => $priceInCents,
            'currency' => $validated['currency'] ?? $gift->currency,
            'url' => $newUrl,
            'allow_multiple_claims' => array_key_exists('most_wanted', $validated) && count($validated) === 1 ? $gift->allow_multiple_claims : $request->boolean('allow_multiple_claims'),
        ];

        if ($urlAdded) {
            $updateData['fetch_status'] = 'pending';
            $updateData['fetch_error'] = null;
            $updateData['fetch_attempts'] = 0;
        }

        $gift->getConnection()->transaction(function () use ($gift, $validated, $updateData): void {
            $gift->refreshForUpdate();
            $edited = array_merge($gift->edited_fields ?? [], $validated['edited_fields'] ?? []);
            foreach (['title', 'description', 'price_in_cents', 'currency', 'shop_name'] as $field) {
                if ($validated['tracks_edits'] ?? false) {
                    if (! in_array($field, $edited, true) && filled($gift->{$field})) {
                        unset($updateData[$field]);
                    }
                } elseif (array_key_exists($field === 'price_in_cents' ? 'price' : $field, $validated)
                    && $updateData[$field] !== $gift->{$field}) {
                    $edited[] = $field;
                }
            }
            $gift->update([...$updateData, 'edited_fields' => array_values(array_unique($edited))]);
        });

        if ($request->session()->get('first_gift') === $gift->id) {
            $request->session()->forget('first_gift');
            $list = $gift->lists()->first();
            if ($list) {
                return redirect()->route('gifts.share', ['locale' => $locale, 'gift' => $gift])
                    ->with('first_gift_share', $list->id);
            }
        }

        return redirect()
            ->to("/{$locale}/dashboard")
            ->with('success', __('Gift updated successfully.'));
    }

    public function share(Request $request, string $locale, Gift $gift): View|RedirectResponse
    {
        $this->authorize('update', $gift);
        $list = $gift->lists()->find($request->session()->get('first_gift_share'));
        if (! $list) {
            return redirect()->route('dashboard.locale', ['locale' => $locale]);
        }

        $gifts = $list->gifts()->with('media')->paginate(24);
        $og = app(OgImage::class);
        $hash = $og->hash(trim(view('public.list-og-image', compact('list', 'gifts'))->render()));
        $og->storeInCache($hash, $list->getPublicUrl());

        return view('gifts.share', ['list' => $list, 'imageUrl' => $og->url($hash)]);
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
            'image_url_card' => $gift->fresh()->getImageUrl('card'),
        ]);
    }

    public function cardHtml(Request $request, string $locale, int $list, string $slug, int $giftId): Response
    {
        $giftList = GiftList::findOrFail($list);

        if ($giftList->slug !== $slug) {
            abort(404);
        }

        $gift = $giftList->gifts()->findOrFail($giftId);

        $isOwner = auth()->check() && $giftList->hasUser(auth()->user());
        $showReservations = ! $isOwner || $giftList->spoiler_mode;
        $gift->load(['claims' => fn ($query) => $query->whereNotNull('confirmed_at')->when(! $showReservations, fn ($query) => $query->whereRaw('1 = 0'))]);
        $html = view('components.shared-gift-tile', [
            'gift' => $gift,
            'isOwner' => $isOwner,
            'isMine' => ! $isOwner && $gift->claims->contains(fn ($claim) => $claim->belongsToBrowser($request)),
            'isReserved' => $showReservations && ! $gift->allow_multiple_claims && $gift->claims->isNotEmpty(),
        ])->render().view('components.gift-modal', compact('gift', 'isOwner', 'showReservations'))->render();

        return response($html)->header('Content-Type', 'text/html')->header('Cache-Control', 'private, no-store');
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
