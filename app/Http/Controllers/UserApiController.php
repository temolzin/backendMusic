<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserApiCreateRequest;
use App\Mail\AdminPasswordChangedNotification;
use App\Models\Role;
use App\Models\User;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class UserApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            //$users = User::orderBy('id', 'Asc')->with('roles:id,name')->get();
            $users = DB::table('users')
                ->join('users_roles', 'users_roles.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
                ->select(
                    'users.name',
                    'users.id',
                    'users.email',
                    'users.created_at',
                    'users_roles.role_id',
                    'roles.name as role_name'
                )->where('users.id', '!=', Auth::user()->id)
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users,
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
    public function store(UserApiCreateRequest $request)
    {
        try {
            $validated = $request->validated();
            $email = $validated['email'];

            DB::beginTransaction();
            $user = new User();
            $user->name = $validated['name'];
            $user->email = $email;
            $user->password = bcrypt($validated['password']);
            $user->save();
            $user->roles()->attach($validated['role_id']);
            DB::commit();

            try {
                $hash = md5(strtolower(trim($email)));
                $absoluteImageUrl = 'https://secure.gravatar.com/avatar/' . $hash . '?s=800&d=retro';
                $user->addMediaFromUrl($absoluteImageUrl)->toMediaCollection('profile_images');
            } catch (Throwable $e) {
                Log::warning('Unable to download the new user Gravatar.', [
                    'user_id' => $user->id,
                    'exception' => $e,
                ]);
            }

            return response()->json([
                'success' => true,
                'user' => $user,
            ], 200);
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible crear el usuario.'
            ], 500);
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
            $user = User::find($id);

            return response()->json([
                'success' => true,
                'user' => $user,
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

            $role_id = $request->input("role_id");

            $role = Role::where('id', $role_id)->first();

            $id = $request->input("id");
            $name = $request->input("name");
            $email = $request->input("email");
            $password = $request->input("password");
            $passwordChanged = $password !== null;

            $user = User::find($id);
            $user->name = $name;
            $user->email = $email;

            if ($passwordChanged) {
                $user->password = bcrypt($password);
            }

            DB::beginTransaction();
            $user->save();
            $user->roles()->sync($role->id);
            DB::commit();

            if ($passwordChanged) {
                Mail::to($user->email)->send(new AdminPasswordChangedNotification($user));
            }

            return response()->json([
                'success' => true,
                'user' => $user,
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
            $user = User::where('id', $id)->first();
            $artist = Artist::where('user_id', $id)->first();
            if ($artist) {
                DB::table('managers')->where('artist_id', $artist->id)->delete();
                DB::table('historys_shoppings')->where('artist_id', $artist->id)->delete();
                DB::table('shoppings_cards_details')->where('artist_id', $artist->id)->delete();
                DB::table('artist_musical_gender')->where('artist_id', $artist->id)->delete();
                DB::table('quotations')->where('artist_id', $artist->id)->delete();
                DB::table('artists')->where('id', $artist->id)->delete();
            }
            $user->delete();

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
