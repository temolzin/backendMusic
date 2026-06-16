<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleMapsController extends Controller
{
    public function getKey()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
            ],
        ], 200);
    }
}
