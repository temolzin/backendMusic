<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserApiCreateRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $users = User::orderBy('id', 'Asc')->get();

            return response()->json([
                'success' => true,
                'users' => $users,
            ],200);
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

            $name = $request->input("name");
            $email = $request->input("email");
            $password = $request->input("password");
            $role_id = $request->input("role_id");

            
            DB::beginTransaction();
                $user = new User();
                $user->name = $name;
                $user->email = $email;
                $user->password = bcrypt($password);
                $user->save();  
                $user->roles()->attach($role_id);
                DB::commit();

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
    public function update(Request $request, $id)
    {
        try {

            $name = $request->input("name");
            $email = $request->input("email");
            $password = $request->input("password");
            $role_id = $request->input("role_id");

            DB::beginTransaction();
            $user = User::find($id);
            $user->name = $name;
            $user->email = $email;
            $user->password = bcrypt($password);
            $user->save();  
            $user->roles()->attach($role_id);
            $user->save();

            DB::commit();

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
