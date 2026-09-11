<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventTypesController extends Controller
{
    public function index()
    {
        try {
            $eventTypes = EventType::orderBy('name', 'Asc')->get();

            return response()->json([
                'success' => true,
                'eventTypes' => $eventTypes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:event_types,name',
        ], [
            'name.unique' => 'El tipo de evento ya se encuentra registrado.',
            'name.required' => 'El nombre del tipo de evento es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $eventType = new EventType();
            $eventType->name = $request->input('name');
            $eventType->slug = Str::slug($request->input('name'));
            $eventType->save();

            return response()->json([
                'success' => true,
                'eventType' => $eventType,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:event_types,name,' . $id,
        ], [
            'name.unique' => 'El tipo de evento ya se encuentra registrado.',
            'name.required' => 'El nombre del tipo de evento es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $eventType = EventType::find($id);
            $eventType->name = $request->input('name');
            $eventType->slug = Str::slug($request->input('name'));
            $eventType->save();

            return response()->json([
                'success' => true,
                'eventType' => $eventType,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function destroy($id)
    {
        try {
            $eventType = EventType::find($id);
            $eventType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de evento borrado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}