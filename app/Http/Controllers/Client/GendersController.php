<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\MusicalGender;
use Illuminate\Http\Request;

class GendersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $musicalGenders = MusicalGender::orderBy('name', 'Asc')->get();

            return response()->json([
                'success' => true,
                'musicalGenders' => $musicalGenders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function artistGenders(Request $request)
    {
        try {
            $artistsGenders = MusicalGender::with('artists')->where('slug',$request->slug)->get();
            return response()->json([
                'success' => true,
                'artistsGenders' => $artistsGenders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

}
