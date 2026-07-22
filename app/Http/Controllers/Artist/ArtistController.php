<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistProfileRequest;
use App\Models\Manager;
use App\Rules\ValidImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidSocialMedia;
use App\Models\ArtistVideo;
use App\Mail\ArtistProfileRequestSubmitted;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

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

            $latestRequest = ArtistProfileRequest::where('user_id', Auth::user()->id) ->latest() ->first();

            return response()->json([
                'success' => true,
                'artists' => $artistMusicalGenders,
                'latestRequest' => $latestRequest,
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

            $existingArtist = Artist::where('user_id', Auth::user()->id)->first();
            $existingPending = ArtistProfileRequest::where('user_id', Auth::user()->id)
                ->where('approval_status', ArtistProfileRequest::APPROVAL_STATUS_PENDING)
                ->exists();

            if ($existingArtist || $existingPending) {
                return response()->json([
                    'success' => false,
                    'message' => $existingPending
                        ? 'Ya tienes una solicitud en revisión.'
                        : 'Ya tienes un perfil de artista registrado.',
                ], 422);
            }

            $proposedData = [
                'name' => $request->input('name'),
                'members' => $request->input('members'),
                'history' => $request->input('history'),
                'zone' => $request->input('zone'),
                'price_hour' => $request->input('price_hour'),
                'extra_kilometre' => $request->input('extra_kilometre'),
                'coverage_radius' => $request->input('coverage_radius', 0),
                'social_media' => $request->input('social_media') ? json_decode($request->input('social_media'), true) : null,
                'musical_genders' => json_decode($request->selection, true),
                'name_manager' => $request->input('name_manager'),
                'phone_manager' => $request->input('phone_manager'),
                'email_manager' => $request->input('email_manager'),
            ];

            DB::beginTransaction();
            $profileRequest = ArtistProfileRequest::create([
                'user_id' => Auth::user()->id,
                'artist_id' => null,
                'request_type' => ArtistProfileRequest::TYPE_CREATION,
                'proposed_data' => $proposedData,
            ]);

            if ($request->hasFile('image_artist')) {
                $profileRequest->addMedia($request->file('image_artist'))->toMediaCollection('pending_artist_image');
            }

            if ($request->hasFile('image_manager')) {
                $profileRequest->addMedia($request->file('image_manager'))->toMediaCollection('pending_manager_image');
            }
            DB::commit();

            $this->sendAdminNotification($profileRequest);

            return response()->json([
                'success' => true,
                'message' => 'Tu solicitud fue enviada, Vibeer la revisará.',
                'profileRequest' => $profileRequest,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hay campos inválidos o faltantes en el formulario.',
                'errors'  => $e->errors(),
            ], 422);
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

            $artist = Artist::find($request->id);

            $existingPending = ArtistProfileRequest::where('artist_id', $artist->id)
                ->where('approval_status', ArtistProfileRequest::APPROVAL_STATUS_PENDING)
                ->exists();

            if ($existingPending) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu perfil está en revisión, no puedes editar hasta que sea aprobado.',
                ], 403);
            }

            $proposedData = [
                'name' => $request->input('name'),
                'members' => $request->input('members'),
                'history' => $request->input('history'),
                'zone' => $request->input('zone'),
                'price_hour' => $request->input('price_hour'),
                'extra_kilometre' => $request->input('extra_kilometre'),
                'coverage_radius' => $request->input('coverage_radius', 0),
                'social_media' => $request->input('social_media') ? json_decode($request->input('social_media'), true) : null,
                'musical_genders' => json_decode($request->selection, true),
                'name_manager' => $request->input('name_manager'),
                'phone_manager' => $request->input('phone_manager'),
                'email_manager' => $request->input('email_manager'),
            ];

            DB::beginTransaction();
            $profileRequest = ArtistProfileRequest::create([
                'user_id' => Auth::user()->id,
                'artist_id' => $artist->id,
                'request_type' => ArtistProfileRequest::TYPE_UPDATE,
                'proposed_data' => $proposedData,
            ]);

            if ($request->hasFile('image_artist')) {
                $profileRequest->addMedia($request->file('image_artist'))->toMediaCollection('pending_artist_image');
            }

            if ($request->hasFile('image_manager')) {
                $profileRequest->addMedia($request->file('image_manager'))->toMediaCollection('pending_manager_image');
            }
            DB::commit();

            $this->sendAdminNotification($profileRequest);

            return response()->json([
                'success' => true,
                'message' => 'Tu solicitud fue enviada, Vibeer la revisará.',
                'profileRequest' => $profileRequest,
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
            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();

            $artistGallery = $artist->getMedia('artist_gallery')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'original_url' => $media->getUrl(),
                ];
            })->values();
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
            $artistGalleryCount = $artist->getMedia('artist_gallery')->count();
    
            if ($artistGalleryCount < 5) {
                if ($request->hasFile('sub_files_paths')) {
                    $uploadedFile = $request->file('sub_files_paths');
    
                    DB::beginTransaction();
                    $media = $artist->addMedia($uploadedFile)->toMediaCollection('artist_gallery');
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Imagen almacenada',
                        'artistGallery' => [
                            'id' => $media->id,
                            'file_name' => $media->file_name,
                            'original_url' => $media->getUrl(),
                        ],
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
            $artistGalleryCount = $artist->getMedia('artist_gallery')->count();
    
            if ($artistGalleryCount < 5) {
                if ($request->hasFile('sub_files_paths')) {
                    $uploadedFile = $request->file('sub_files_paths');
    
                    DB::beginTransaction();
                    $media = $artist->addMedia($uploadedFile)->toMediaCollection('artist_gallery');
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Imagen actualizada',
                        'artistGallery' => [
                            'id' => $media->id,
                            'file_name' => $media->file_name,
                            'original_url' => $media->getUrl(),
                        ],
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
            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();

            DB::beginTransaction();
            $artist->clearMediaCollection('artist_gallery');
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Imagenes eliminadas'
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

    private function sendAdminNotification(ArtistProfileRequest $profileRequest)
    {
        try {
            Mail::to(config('mail.from.address'))->send(new ArtistProfileRequestSubmitted($profileRequest));
        } catch (\Throwable $e) {
            Log::warning('Error enviando notificación de solicitud de artista a Vibeer: ' . $e->getMessage());
        }
    }
}
