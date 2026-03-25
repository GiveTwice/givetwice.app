<?php

namespace App\Http\Controllers;

use App\Jobs\RecordAffiliateClick;
use App\Models\Gift;
use App\Services\AffiliateUrlTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AffiliateRedirectController extends Controller
{
    public function __invoke(Request $request, Gift $gift, AffiliateUrlTransformer $transformer): Response
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

        return response()->view('affiliate.redirect', [
            'affiliateUrl' => $affiliateUrl,
            'fallbackUrl' => $gift->url,
            'storeName' => $gift->siteName() ?? parse_url($gift->url, PHP_URL_HOST) ?? 'the store',
        ]);
    }
}
