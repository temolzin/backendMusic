<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArtistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $artists = Artist::where('user_id', '=', Auth::user()->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'artists' => $artists,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            //DB::beginTransaction();
            $artist =  Artist::create([
            'user_id' => Auth::user()->id,
            'name' => $request->input('name'),
            'members' => $request->input('members'),
            'history' => $request->input('history'),
            'zone' => $request->input('zone'),
            'price_hour' => $request->input('price_hour'),
            //$artist->image = $request->input('image');
            'extra_kilometre' => $request->input('extra_kilometre'),
            //$artist->points = $request->input('points');
            ]);
            
            //DB::commit();
            $manager = Manager::create([
                'artist_id' => $artist->id,
                'name' => $request->input('name_manager'),
                'phone' => $request->input('phone_manager'),
                'email' => $request->input('email_manager'),
                //'image' => $request->input('image_manager'),
            ]);
            //DB::beginTransaction();
            

            return response()->json([
                'success' => true,
                'artist' => $artist,
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $artist = Artist::find($id);

            return response()->json([
                'success' => true,
                'artist' => $artist,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $artist = Artist::find($id);
            $artist->fill($request->all());
            $artist->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'artist' => $artist,
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
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $artist = Artist::where('id', $id)->first();
            $artist->delete();

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
