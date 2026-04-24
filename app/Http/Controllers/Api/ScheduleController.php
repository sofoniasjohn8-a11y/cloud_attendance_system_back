<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules
     */
    public function index(Request $request)
    {
        $query = Schedule::with('user');
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $schedules = $query->get();
        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    /**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'required|date_format:H:i:s',
                'end_time' => 'required|date_format:H:i:s|after:start_time',
            ]);

            // Check unique constraint
            $exists = Schedule::where('user_id', $validated['user_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->exists();
                
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule already exists for this user on this day'
                ], 422);
            }

            $schedule = Schedule::create($validated);
            return response()->json([
                'success' => true,
                'data' => $schedule->load('user'),
                'message' => 'Schedule created successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified schedule
     */
    public function show(string $id)
    {
        $schedule = Schedule::with('user')->find($id);
        
        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, string $id)
    {
        try {
            $schedule = Schedule::find($id);
            
            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found'
                ], 404);
            }

            $validated = $request->validate([
                'day_of_week' => 'nullable|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'nullable|date_format:H:i:s',
                'end_time' => 'nullable|date_format:H:i:s',
            ]);

            $schedule->update($validated);
            return response()->json([
                'success' => true,
                'data' => $schedule->load('user'),
                'message' => 'Schedule updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified schedule
     */
    public function destroy(string $id)
    {
        $schedule = Schedule::find($id);
        
        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found'
            ], 404);
        }

        $schedule->delete();
        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully'
        ]);
    }
}
