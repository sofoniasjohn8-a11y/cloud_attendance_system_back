<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    /**
     * Display a listing of offices
     */
    public function index()
    {
        $offices = Office::all();
        return response()->json([
            'success' => true,
            'data' => $offices
        ]);
    }

    /**
     * Store a newly created office
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:offices',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius_meters' => 'nullable|integer|min:10',
            ]);

            $office = Office::create($validated);
            return response()->json([
                'success' => true,
                'data' => $office,
                'message' => 'Office created successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified office
     */
    public function show(string $id)
    {
        $office = Office::find($id);
        
        if (!$office) {
            return response()->json([
                'success' => false,
                'message' => 'Office not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $office
        ]);
    }

    /**
     * Update the specified office
     */
    public function update(Request $request, string $id)
    {
        try {
            $office = Office::find($id);
            
            if (!$office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Office not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'nullable|string|unique:offices,name,' . $id,
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'radius_meters' => 'nullable|integer|min:10',
            ]);

            $office->update($validated);
            return response()->json([
                'success' => true,
                'data' => $office,
                'message' => 'Office updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified office
     */
    public function destroy(string $id)
    {
        $office = Office::find($id);
        
        if (!$office) {
            return response()->json([
                'success' => false,
                'message' => 'Office not found'
            ], 404);
        }

        $office->delete();
        return response()->json([
            'success' => true,
            'message' => 'Office deleted successfully'
        ]);
    }
}
