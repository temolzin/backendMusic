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
            $artists = DB::table('artists')
                ->join('managers', 'artists.id', '=', 'managers.artist_id')
                ->select(
                    'artists.id',
                    'artists.user_id',
                    'artists.name',
                    'artists.members',
                    'artists.history',
                    'artists.zone',
                    'artists.price_hour',
                    'artists.image',
                    'artists.extra_kilometre',
                    'artists.points',
                    'artists.created_at',
                    'managers.id',
                    'managers.name as name_manager',
                    'managers.phone',
                    'managers.email',
                    'managers.image',
                )->where('artists.user_id', '=', Auth::user()->id)
                ->get();

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
            $artist = Artist::find($request->input('id'));
            $artist->name = $request->input('name');
            $artist->members = $request->input('members');
            $artist->history = $request->input('history');
            $artist->zone = $request->input('zone');
            $artist->price_hour = $request->input('price_hour');
            $artist->extra_kilometre = $request->input('extra_kilometre');
            $artist->manager->name = $request->input('name_manager');
            $artist->manager->phone = $request->input('phone_manager');
            $artist->manager->email = $request->input('email_manager');
            $artist->push();
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
        // try {
        //     DB::beginTransaction();
        //     $artist = Artist::where('id', $id)->first();
        //     $artist->delete();

        //     DB::commit();
        //     return response()->json([
        //         'success' => true,
        //     ], 200);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return response()->json([
        //         'success' => false,
        //         'message' => $e->getMessage()
        //     ], 401);
        // }
    }
}
