<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;

class ArtistsGeneralController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function latestArtists()
    {
        try {
            $artists = Artist::orderBy('id', 'DESC')->with('musicalGenders')->take(3)->get();

            return response()->json([
                'success' => true,
                'latestArtists' => $artists,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
