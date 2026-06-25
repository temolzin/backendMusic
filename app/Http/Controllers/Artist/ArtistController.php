<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\GaleryArtist;
use App\Models\Manager;
use App\Rules\ValidImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Rules\ValidSocialMedia;
use App\Models\ArtistVideo;
use Carbon\Carbon;

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
            $artistMusicalGenders = Artist::with('musicalGenders')->with('manager')->where('user_id', Auth::user()->id)->first();

            return response()->json([
                'success' => true,
                'artists' => $artistMusicalGenders,
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
                'name'            => 'required',
                'members'         => 'required',
                'history'         => 'required',
                'zone'            => 'required',
                'price_hour'      => 'required',
                'image_artist'    => ['required', 'file', 'max:20480', new ValidImageUpload()],
                'extra_kilometre' => 'required',
                'coverage_radius' => 'nullable|integer|min:0',
                'name_manager'    => 'required',
                'phone_manager'   => 'required',
                'email_manager'   => 'required|email',
                'image_manager'   => ['required', 'file', 'max:20480', new ValidImageUpload()],
            ]);

            $urlStoreArtist = Storage::put('public/artist', request()->file('image_artist'));
            $linkArtist = url(Storage::url($urlStoreArtist));

            DB::beginTransaction();
            $artist = Artist::create([
                'user_id' => Auth::user()->id,
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'members' => $request->input('members'),
                'history' => $request->input('history'),
                'zone' => $request->input('zone'),
                'price_hour' => $request->input('price_hour'),
                'image' => $linkArtist,
                'extra_kilometre' => $request->input('extra_kilometre'),
                'coverage_radius' => $request->input('coverage_radius', 0),
                'social_media' => $request->input('social_media') ? json_decode($request->input('social_media'), true) : null,
            ]);

            $artist->musicalGenders()->sync(json_decode($request->selection));
            $urlStoreManager = Storage::put('public/manager', request()->file('image_manager'));
            $linkManager = url(Storage::url($urlStoreManager));

            Manager::create([
                'artist_id' => $artist->id,
                'name'      => $request->input('name_manager'),
                'phone'     => $request->input('phone_manager'),
                'email'     => $request->input('email_manager'),
                'image'     => $linkManager,
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'artist'  => $artist,
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
            $artist->coverage_radius = $request->input('coverage_radius', 0);
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
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateDetails(Request $request)
    {
        try {
            $request->validate([
                'name'            => 'required',
                'members'         => 'required',
                'history'         => 'required',
                'zone'            => 'required',
                'price_hour'      => 'required',
                'image_artist'    => ['nullable', 'file', 'max:20480', new ValidImageUpload()],
                'extra_kilometre' => 'required',
                'coverage_radius' => 'nullable|integer|min:0',
                'name_manager'    => 'required',
                'phone_manager'   => 'required',
                'email_manager'   => 'required|email',
                'image_manager'   => ['nullable', 'file', 'max:20480', new ValidImageUpload()],
                'social_media' => ['nullable', new ValidSocialMedia()],
            ]);

            DB::beginTransaction();
            $artist = Artist::find($request->id);

            $artist->name = $request->input('name');
            $artist->slug = Str::slug($request->input('name'));
            $artist->members = $request->input('members');
            $artist->history = $request->input('history');
            $artist->zone = $request->input('zone');
            $artist->price_hour = $request->input('price_hour');
            $artist->extra_kilometre = $request->input('extra_kilometre');
            $artist->coverage_radius = $request->input('coverage_radius', 0);
            $artist->social_media = $request->input('social_media') ? json_decode($request->input('social_media'), true) : null;
            $linkArtist = $artist->image;
            $linkManager = $artist->manager->image;

            if (request()->file('image_artist')) {
                $urlStore = Storage::put('public/artist', request()->file('image_artist'));
                $linkArtistNew = url(Storage::url($urlStore));
                $img = str_replace('storage', 'public', $linkArtist);
                $less = env('APP_URL') . '/public/';
                $img = str_replace($less, '', $img);
                Storage::delete($img);
                $linkArtist = $linkArtistNew;
            }

            if (request()->file('image_manager')) {
                $urlStore = Storage::put('public/manager', request()->file('image_manager'));
                $linkManagerNew = url(Storage::url($urlStore));
                $img = str_replace('storage', 'public', $linkManager);
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
            $artist->musicalGenders()->sync(json_decode($request->selection));

            return response()->json([
                'success' => true,
                'artist'  => $artist,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function artistGalleryIndex()
    {
        try {
            $artist_id = Artist::where('user_id', Auth::user()->id)->firstOrFail();
            $artistGallery = GaleryArtist::where('artist_id', $artist_id->id)->get();
            return response()->json([
                'success' => true,
                'artistGallery' => $artistGallery,
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
    public function storeGaleryArtist(Request $request)
    {
        $request->validate([
            'sub_files_paths' => ['required', 'file', 'max:1024', new ValidImageUpload()],
        ]);
    
        try {
            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();
            $artistGalleryCount = GaleryArtist::where('artist_id', $artist->id)->count();
    
            if ($artistGalleryCount < 5) {
                if ($request->hasFile('sub_files_paths')) {
                    $uploadedFile = $request->file('sub_files_paths');
                    $urlStore = Storage::put('public/galery-artist', $uploadedFile);
                    $linkGalleryNew = Storage::url($urlStore);
                    $absolutePath = url($linkGalleryNew);
    
                    DB::beginTransaction();
                    $gallery = GaleryArtist::create([
                        'artist_id' => $artist->id,
                        'image' => $absolutePath,
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Imagen almacenada',
                        'artistGallery' => $gallery,
                    ], 201);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Máximo de imágenes almacenadas"
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateGaleryArtist(Request $request)
    {
        $request->validate([
            'sub_files_paths' => ['required', 'file', 'max:1024', new ValidImageUpload()],
        ]);
    
        try {
            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();
            $artistGalleryCount = GaleryArtist::where('artist_id', $artist->id)->count();
    
            if ($artistGalleryCount < 5) {
                if ($request->hasFile('sub_files_paths')) {
                    $uploadedFile = $request->file('sub_files_paths');
                    $urlStore = Storage::put('public/galery-artist', $uploadedFile);
                    $linkGalleryNew = Storage::url($urlStore);
                    $absolutePath = url($linkGalleryNew);
    
                    DB::beginTransaction();
                    $gallery = GaleryArtist::create([
                        'artist_id' => $artist->id,
                        'image' => $absolutePath,
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Imagen actualizada',
                        'artistGallery' => $gallery,
                    ], 201);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Máximo de imágenes almacenadas"
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteGaleryArtist(Request  $request)
    {
        try {
            $artist_id = Artist::where('user_id', Auth::user()->id)->firstOrFail();
            $artistGallery = GaleryArtist::where('artist_id', $artist_id->id)->get();
            foreach ($artistGallery as $artist) {
                $img = $artist->image;
                $img = str_replace('storage', 'public', $img);
                $less = env('APP_URL') . '/public/';
                $img = str_replace($less, '', $img);
                Storage::delete($img);
                DB::beginTransaction();
                $artist = GaleryArtist::where('id', $artist->id)->first();
                $artist->delete();
                DB::commit();
            }
            return response()->json([
                'success' => true,
                'message' => 'Imagenes eiminadas'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }


    /**
     * Display a listing of the resource of all Artists with Musical Genders.
     *
     * @return \Illuminate\Http\Response
     */
    public function getArtist()
    {
        try {
            $artistWithMusicalGender = Artist::with([
                'musicalGenders',
                'offers' => function ($query) {
                $query->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
                }
            ])
                ->withAvg("ratings","rating")
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'artists' => $artistWithMusicalGender,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function artistVideosIndex()
    {
        try {
            $artist_id = Artist::where('user_id', Auth::user()->id)->first();
            $videos = ArtistVideo::where('artist_id', $artist_id->id)->get();

            return response()->json([
                'success' => true,
                'artistVideos' => $videos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeArtistVideo(Request $request)
    {
        try {
            $artist = Artist::where('user_id', Auth::user()->id)->first();
            $count = ArtistVideo::where('artist_id', $artist->id)->count();

            if ($count >= 3) {
                return response()->json(['message' => 'Máximo 3 videos permitidos'], 422);
            }

            $url = $request->youtube_url;
            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);

            if (empty($matches[1])) {
                return response()->json(['message' => 'URL de YouTube no válida'], 422);
            }

            $video = ArtistVideo::create([
                'artist_id'   => $artist->id,
                'youtube_url' => $matches[1],
                'title'       => $oembed['title'] ?? null,
                'thumbnail'   => $oembed['thumbnail_url'] ?? "https://img.youtube.com/vi/{$matches[1]}/hqdefault.jpg",
            ]);

            return response()->json(['success' => true, 'artistVideo' => $video], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteArtistVideo($id)
    {
        try {
            $artist = Artist::where('user_id', Auth::user()->id)->first();
            $video = ArtistVideo::where('id', $id)->where('artist_id', $artist->id)->firstOrFail();
            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video eliminado',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
