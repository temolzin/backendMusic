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

            $roleIds = $request->input('role_ids', []);
            $subject = $request->input('subject');
            $content = $request->input('content');
            $emailsToSend = []; 

            if (in_array('newsletter_users', $roleIds)) {
                $subscriberEmails = UsersSubscribe::pluck('email')->toArray();
                $emailsToSend = array_merge($emailsToSend, $subscriberEmails);
                $roleIds = array_diff($roleIds, ['newsletter_users']);
            }

            if (count($roleIds) > 0) {
                $userEmails = \App\Models\User::whereHas('roles', function($query) use ($roleIds) {
                    $query->whereIn('id', $roleIds);
                })->pluck('email')->toArray();
                $emailsToSend = array_merge($emailsToSend, $userEmails);
            }
            $emailsToSend = array_unique($emailsToSend);

            foreach ($emailsToSend as $email) {
                Mail::to($email)->send(new SendNewsletter($subject, $content));
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
