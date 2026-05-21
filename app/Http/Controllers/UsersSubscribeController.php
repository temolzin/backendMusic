<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UsersSubscribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNewsletter;

class UsersSubscribeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $emailSuscribe = UsersSubscribe::pluck('email');

            return response()->json([
                'success' => true,
                'emails' => $emailSuscribe,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 402);
        }
    }

    /**
     * Add a new email to Users Suscribed.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $UserSuscribe = new UsersSubscribe();
            $UserSuscribe->email = $request->input('email');
            $UserSuscribe->user_id = auth()->id();
            $UserSuscribe->save();

            DB::commit();

            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send email to all users subscribed.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendEmailToSubscribers(Request $request)
    {
        try {
            $validated = $request->validate([
                'subject' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string'],
                'role_ids' => ['required', 'array', 'min:1'],
                'role_ids.*' => ['integer', 'exists:roles,id'],
            ]);

            $roleIds = collect($validated['role_ids'])->unique()->values()->all();

            $emailSubscribers = UsersSubscribe::whereHas('user.roles', function ($query) use ($roleIds) {
                $query->whereIn('roles.id', $roleIds);
            })
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

            if (empty($emailSubscribers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay destinatarios disponibles con los roles seleccionados.',
                    'errors' => [
                        'role_ids' => ['No hay destinatarios disponibles con esos roles.'],
                    ],
                ], 422);
            }

            foreach ($emailSubscribers as $email) {
                Mail::to($email)->send(new SendNewsletter($validated['subject'], $validated['content']));
            }

            return response()->json([
                'success' => true,
                'message' => 'Correos electrónicos enviados correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
