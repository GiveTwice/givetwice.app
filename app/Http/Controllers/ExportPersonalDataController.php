<?php

namespace App\Http\Controllers;

use App\Actions\ExportPersonalDataAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportPersonalDataController extends Controller
{
    public function __invoke(Request $request, ExportPersonalDataAction $action): Response
    {
        $user = $request->user();
        $data = $action->execute($user);

        $filename = 'givetwice-data-export-'.now()->format('Y-m-d').'.json';
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
