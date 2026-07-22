<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ArtistProfileReviewed;
use App\Models\Artist;
use App\Models\ArtistProfileRequest;
use App\Models\Manager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ArtistApprovalController extends Controller
{
    public function pendingRequests()
    {
        try {
            $pending = ArtistProfileRequest::where('approval_status', ArtistProfileRequest::APPROVAL_STATUS_PENDING)
                ->with(['user', 'artist.manager', 'artist.musicalGenders'])
                ->oldest()
                ->get();

            return response()->json(['success' => true, 'requests' => $pending]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function history()
    {
        try {
            $history = ArtistProfileRequest::whereIn('approval_status', [
                    ArtistProfileRequest::APPROVAL_STATUS_ACCEPTED,
                    ArtistProfileRequest::APPROVAL_STATUS_REJECTED,
                ])
                ->with(['user', 'artist.manager', 'artist.musicalGenders', 'authorizedByUser'])
                ->latest('reviewed_at')
                ->get();

            return response()->json(['success' => true, 'requests' => $history]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function accept($id)
    {
        try {
            $profileRequest = ArtistProfileRequest::where('id', $id)
                ->where('approval_status', ArtistProfileRequest::APPROVAL_STATUS_PENDING)
                ->first();

            if (!$profileRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya no está pendiente (puede que ya se haya revisado). Actualiza la lista.',
                ], 404);
            }

            $data = $profileRequest->proposed_data;

            DB::beginTransaction();

            $artist = $profileRequest->request_type === ArtistProfileRequest::TYPE_CREATION
                ? new Artist(['user_id' => $profileRequest->user_id])
                : Artist::findOrFail($profileRequest->artist_id);

            $artist->name = $data['name'];
            $artist->slug = Str::slug($data['name']);
            $artist->members = $data['members'];
            $artist->history = $data['history'];
            $artist->zone = $data['zone'];
            $artist->price_hour = $data['price_hour'];
            $artist->extra_kilometre = $data['extra_kilometre'];
            $artist->coverage_radius = $data['coverage_radius'] ?? 0;
            $artist->social_media = $data['social_media'] ?? null;
            $artist->save();

            $pendingArtistImage = $profileRequest->getFirstMedia('pending_artist_image');
            if ($pendingArtistImage) {
                $pendingArtistImage->copy($artist, 'artist_image');
            }

            $manager = $artist->manager ?: new Manager(['artist_id' => $artist->id]);
            $manager->artist_id = $artist->id;
            $manager->name = $data['name_manager'];
            $manager->phone = $data['phone_manager'];
            $manager->email = $data['email_manager'];
            $manager->save();

            $pendingManagerImage = $profileRequest->getFirstMedia('pending_manager_image');
            if ($pendingManagerImage) {
                $pendingManagerImage->copy($manager, 'manager_image');
            }

            if (!empty($data['musical_genders'])) {
                $artist->musicalGenders()->sync($data['musical_genders']);
            }

            $profileRequest->artist_id = $artist->id;
            $profileRequest->approval_status = ArtistProfileRequest::APPROVAL_STATUS_ACCEPTED;
            $profileRequest->reviewed_at = Carbon::now();
            $profileRequest->authorized_by = Auth::id();
            $profileRequest->save();

            DB::commit();

            $this->sendReviewNotification($profileRequest, ArtistProfileRequest::APPROVAL_STATUS_ACCEPTED);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aceptada, el artista ya es visible en tienda.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $profileRequest = ArtistProfileRequest::where('id', $id)
                ->where('approval_status', ArtistProfileRequest::APPROVAL_STATUS_PENDING)
                ->first();

            if (!$profileRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya no está pendiente (puede que ya se haya revisado). Actualiza la lista.',
                ], 404);
            }

            $profileRequest->approval_status = ArtistProfileRequest::APPROVAL_STATUS_REJECTED;
            $profileRequest->rejection_reason = $request->input('rejection_reason');
            $profileRequest->reviewed_at = Carbon::now();
            $profileRequest->authorized_by = Auth::id();
            $profileRequest->save();

            $this->sendReviewNotification($profileRequest, ArtistProfileRequest::APPROVAL_STATUS_REJECTED);

            return response()->json(['success' => true, 'message' => 'Solicitud rechazada.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function sendReviewNotification(ArtistProfileRequest $profileRequest, string $status)
    {
        try {
            Mail::to($profileRequest->user->email)->send(new ArtistProfileReviewed($profileRequest, $status));
        } catch (\Throwable $e) {
            Log::warning('Error enviando notificación al artista: ' . $e->getMessage());
        }
    }
}
