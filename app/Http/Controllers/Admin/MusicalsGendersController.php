<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MusicalGender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MusicalsGendersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $musicalGenders = MusicalGender::orderBy('name', 'Asc')->get();

            return response()->json([
                'success' => true,
                'musicalGenders' => $musicalGenders,
            ]);
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
            $slug = Str::slug($request->input('name'));
            DB::beginTransaction();

            $musicalGenders = new MusicalGender();
            $musicalGenders->name = $request->input('name');
            $musicalGenders->slug = $slug;
            $musicalGenders->description = $request->input('description');
            $musicalGenders->color = $request->input('color');
            $musicalGenders->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'musicalGenders' => $musicalGenders,
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
        //
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
            $slug = Str::slug($request->input('name'));
            DB::beginTransaction();
            $musicalGenders =  MusicalGender::find($id);
            $musicalGenders->name = $request->input('name');
            $musicalGenders->slug = $slug;
            $musicalGenders->description = $request->input('description');
            $musicalGenders->color = $request->input('color');
            $musicalGenders->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'musicalGenders' => $musicalGenders,
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
            $product = MusicalGender::where('id', $id)->first();
            $product->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Genero borrado correctamente'
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
