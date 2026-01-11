<?php

namespace App\Http\Controllers;

use App\Helpers\OccasionHelper;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowOccasionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $occasion = $request->route('occasion');
        $data = OccasionHelper::getPageContent($occasion);

        if (! $data) {
            abort(404);
        }

        return view('pages.occasion', compact('occasion', 'data'));
    }
}
