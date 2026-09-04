<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UsersSubscribe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
            $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => [
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value !== 0 && !DB::table('roles')->where('id', $value)->exists()) {
                        $fail('Uno de los roles seleccionados no existe.');
                    }
                },
            ],
        ]);

        try {
            Gate::authorize('send-newsletters');

            $roleIds = collect($validated['role_ids'])->unique()->values()->all();
            
            $finalEmailList = [];

            $validRoleIds = array_filter($roleIds, function($id) {
                return $id !== 0;
            });

            if (!empty($validRoleIds)) {
                $emailsByRole = User::whereHas('roles', function ($query) use ($validRoleIds) {
                    $query->whereIn('roles.id', $validRoleIds);
                })
                ->pluck('email')
                ->toArray();
                
                $finalEmailList = array_merge($finalEmailList, $emailsByRole);
            }

            if (in_array(0, $roleIds)) {
                $guestEmails = UsersSubscribe::pluck('email')->toArray();
                $finalEmailList = array_merge($finalEmailList, $guestEmails);
            }

            $emailSubscribers = collect($finalEmailList)->filter()->unique()->values()->all();

            if (empty($emailSubscribers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay destinatarios disponibles con las opciones seleccionadas.',
                    'errors' => [
                        'role_ids' => ['No hay destinatarios disponibles con esos roles.'],
                    ],
                ], 422);
            }

            $subject = $validated['subject'];
            $content = $validated['content'];

            foreach ($emailSubscribers as $email) {
                try {
                    Mail::to($email)->queue(new SendNewsletter($subject, $content));
                } catch (\Throwable $th) {
                    continue;
                }
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
