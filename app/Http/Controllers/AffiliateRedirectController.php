<?php

namespace App\Http\Controllers;

use App\Jobs\RecordAffiliateClick;
use App\Models\Gift;
use App\Services\AffiliateUrlTransformer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AffiliateRedirectController extends Controller
{
    public function __invoke(Request $request, Gift $gift, AffiliateUrlTransformer $transformer): RedirectResponse
    {
        if (! $gift->url) {
            abort(404);
        }

        $listId = $request->integer('list') ?: $gift->lists()->first()?->id;
        $affiliateUrl = $transformer->transform($gift->url, $gift->id, $listId);

        RecordAffiliateClick::dispatch(
            giftId: $gift->id,
            listId: $listId,
            url: $gift->url,
            affiliateUrl: $affiliateUrl,
            retailerDomain: $gift->siteName() ?? 'unknown',
            ipHash: hash('sha256', $request->ip() ?? '0.0.0.0'),
            userAgent: $request->userAgent(),
            clickedAt: now(),
        );

        return redirect()->away($affiliateUrl);
    }
}
