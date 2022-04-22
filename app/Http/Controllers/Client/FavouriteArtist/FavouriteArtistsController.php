<?php

namespace App\Http\Controllers\Client\FavouriteArtist;

use App\Http\Controllers\Controller;
use App\Models\FavouriteArtists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavouriteArtistsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $favouriteArtists = FavouriteArtists::with('artist')->where('user_id', Auth::user()->id)->get();
            return response()->json([
                'success' => true,
                'client' => $favouriteArtists,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $message = "";
            $artist_id = $request->input('artist_id');

            $existFavouriteArtist = FavouriteArtists::where('user_id', Auth::user()->id)->where('artist_id', $artist_id)->first();
            if (!$existFavouriteArtist) {
                $favouriteArtist = new FavouriteArtists();
                $favouriteArtist->user_id = Auth::user()->id;
                $favouriteArtist->artist_id = $artist_id;
                $favouriteArtist->save();
                $message = "Agregado a favoritos";
            } else {
                $favouriteArtists = FavouriteArtists::where('artist_id', $artist_id)->where('user_id', Auth::user()->id)->first();
                $favouriteArtists->delete();
                $message = "Eliminado de Favoritos";
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyFavourite($id)
    {
        try {
            DB::beginTransaction();
            $favouriteArtists = FavouriteArtists::where('artist_id', $id)->where('user_id', Auth::user()->id)->first();
            $favouriteArtists->delete();
            DB::commit();
            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
