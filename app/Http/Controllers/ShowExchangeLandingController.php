<?php

namespace App\Http\Controllers;

use App\Helpers\ExchangeHelper;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowExchangeLandingController extends Controller
{
    public function __invoke(Request $request): View
    {
        $key = $request->route('exchangeLandingKey');
        $data = ExchangeHelper::getPageContent($key, $request->route('locale'));

        if (! $data) {
            abort(404);
        }

        return view('pages.exchange-landing', compact('key', 'data'));
    }
}
