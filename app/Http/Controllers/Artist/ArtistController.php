<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                    'artists.image as image_artist',
                    'artists.extra_kilometre',
                    'artists.points',
                    'artists.created_at',
                    'managers.id',
                    'managers.name as name_manager',
                    'managers.phone',
                    'managers.email',
                    'managers.image as image_manager',
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
            $request->validate([
                'name'    => 'required',
                'members' => 'required',
                'history' => 'required',
                'zone'    => 'required',
                'price_hour'      => 'required',
                'image_artist'    => 'required|image|max:1024',
                'extra_kilometre' => 'required',
                'name_manager'    => 'required',
                'phone_manager'   => 'required',
                'email_manager'   => 'required|email',
                'image_manager' => 'required|image|max:1024',
            ]);

            $urlStoreArtist = Storage::put('public/artist', request()->file('image_artist'));
            $linkArtist = Storage::url($urlStoreArtist);

            //DB::beginTransaction();
            $artist =  Artist::create([
                'user_id' => Auth::user()->id,
                'name' => $request->input('name'),
                'members' => $request->input('members'),
                'history' => $request->input('history'),
                'zone' => $request->input('zone'),
                'price_hour' => $request->input('price_hour'),
                'image' => $linkArtist,
                'extra_kilometre' => $request->input('extra_kilometre'),
                //$artist->points = $request->input('points');
            ]);

            $urlStoreManager = Storage::put('public/manager', request()->file('image_manager'));
            $linkManager = Storage::url($urlStoreManager);

            //DB::commit();
            $manager = Manager::create([
                'artist_id' => $artist->id,
                'name' => $request->input('name_manager'),
                'phone' => $request->input('phone_manager'),
                'email' => $request->input('email_manager'),
                'image' => $linkManager,
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
    public function update(Request $request)
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

    public function updateDetails(Request $request)
    {
        try {
            $request->validate([
                'name'    => 'required',
                'members' => 'required',
                'history' => 'required',
                'zone'    => 'required',
                'price_hour'      => 'required',
                'image_artist'    => 'image|max:1024',
                'extra_kilometre' => 'required',
                'name_manager'    => 'required',
                'phone_manager'   => 'required',
                'email_manager'   => 'required|email',
                'image_manager' => 'image|max:1024',
            ]);

            DB::beginTransaction();
            $artist = Artist::find($request->id);

            $artist->name = $request->input('name');
            $artist->members = $request->input('members');
            $artist->history = $request->input('history');
            $artist->zone = $request->input('zone');
            $artist->price_hour = $request->input('price_hour');
            $artist->extra_kilometre = $request->input('extra_kilometre');

            $linkArtist =  $artist->image;
            $linkManager =  $artist->manager->image;

            if (request()->file('image_artist')) {
                $urlStore = Storage::put('public/artist', request()->file('image_artist'));
                $linkArtistNew = Storage::url($urlStore);
                $img = $artist->image;
                $img = str_replace('storage', 'public', $img);
                $less = env('APP_URL') . '/public/';
                $img = str_replace($less, '', $img);
                Storage::delete($img);
                $linkArtist = $linkArtistNew;
            }

            if (request()->file('image_manager')) {
                $urlStore = Storage::put('public/manager', request()->file('image_manager'));
                $linkManagerNew = Storage::url($urlStore);
                $img = $artist->manager->image;
                $img = str_replace('storage', 'public', $img);
                $less = env('APP_URL') . '/public/';
                $img = str_replace($less, '', $img);
                Storage::delete($img);
                $linkManager = $linkManagerNew;
            }
            $artist->image = $linkArtist;
            $artist->manager->image = $linkManager;

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
}
