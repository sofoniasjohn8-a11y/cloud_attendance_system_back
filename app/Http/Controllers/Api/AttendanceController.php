<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendances
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['user', 'office']);
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('office_id')) {
            $query->where('office_id', $request->office_id);
        }
        
        if ($request->has('date')) {
            $query->whereDate('work_date', $request->date);
        }
        
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('work_date', [$request->from, $request->to]);
        }
        
        $attendances = $query->orderBy('work_date', 'desc')->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $attendances->items(),
            'pagination' => [
                'total' => $attendances->total(),
                'per_page' => $attendances->perPage(),
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage()
            ]
        ]);
    }

    /**
     * Clock In - Create new attendance record per schedule slot
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude'    => 'required|numeric|between:-90,90',
                'longitude'   => 'required|numeric|between:-180,180',
                'schedule_id' => 'nullable|exists:schedules,id',
                'remarks'     => 'nullable|string',
            ]);

            $userId    = auth()->id();
            $user      = auth()->user()->load('office');
            $today     = now()->toDateString();
            $dayOfWeek = now()->format('l');
            $now       = now();

            // Get office from user's assigned office
            $office = $user->office;
            if (!$office) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to any office. Contact your admin.',
                ], 422);
            }

            // Find the matching schedule for this clock-in
            if (!empty($validated['schedule_id'])) {
                $schedule = Schedule::where('id', $validated['schedule_id'])
                    ->where('user_id', $userId)
                    ->first();
            } else {
                // Auto-detect: find the schedule whose window covers now (start_time - 30min buffer)
                $schedule = Schedule::where('user_id', $userId)
                    ->where('day_of_week', $dayOfWeek)
                    ->whereRaw("start_time <= ?::time", [$now->addMinutes(30)->format('H:i:s')])
                    ->whereRaw("end_time > ?::time", [$now->subMinutes(30)->format('H:i:s')])
                    ->first();
            }

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedule found for this time slot.',
                ], 422);
            }

            // Check if already clocked in for this specific schedule slot today
            $existing = Attendance::where('user_id', $userId)
                ->whereDate('work_date', $today)
                ->where('schedule_id', $schedule->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already clocked in for this schedule slot. Clock out first.',
                    'attendance' => $existing,
                ], 422);
            }

            // Geofence check
            $distance = $this->calculateDistance(
                $validated['latitude'], $validated['longitude'],
                $office->latitude, $office->longitude
            );

            if ($distance > $office->radius_meters) {
                return response()->json([
                    'success'        => false,
                    'message'        => 'You are outside the office geofence. Distance: ' . round($distance, 2) . 'm from office.',
                    'distance'       => round($distance, 2),
                    'allowed_radius' => $office->radius_meters,
                ], 422);
            }

            // Determine status based on schedule start_time
            $clockInTime   = now()->format('H:i:s');
            $scheduleStart = $schedule->start_time;
            $status = $clockInTime <= $scheduleStart ? 'present' : 'late';

            $attendance = Attendance::create([
                'user_id'     => $userId,
                'office_id'   => $office->id,
                'schedule_id' => $schedule->id,
                'work_date'   => $today,
                'clock_in'    => now(),
                'lat_in'      => $validated['latitude'],
                'lng_in'      => $validated['longitude'],
                'status'      => $status,
                'remarks'     => $validated['remarks'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'data'    => $attendance->load(['user', 'office', 'schedule']),
                'message' => 'Clocked in successfully. Status: ' . $status,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified attendance record
     */
    public function show(string $id)
    {
        $attendance = Attendance::with(['user', 'office'])->find($id);
        
        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Clock Out - Update attendance record
     */
    public function update(Request $request, string $id)
    {
        try {
            $attendance = Attendance::find($id);
            
            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found'
                ], 404);
            }

            if ($attendance->clock_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already clocked out'
                ], 422);
            }

            $validated = $request->validate([
                'clock_out' => 'required|date_format:Y-m-d H:i:s|after:' . $attendance->clock_in->format('Y-m-d H:i:s'),
                'remarks' => 'nullable|string',
            ]);

            $attendance->update([
                'clock_out' => $validated['clock_out'],
                'remarks' => $validated['remarks'] ?? $attendance->remarks,
            ]);

            return response()->json([
                'success' => true,
                'data' => $attendance->load(['user', 'office']),
                'message' => 'Clocked out successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(string $id)
    {
        $attendance = Attendance::find($id);
        
        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        $attendance->delete();
        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully'
        ]);
    }

    /**
     * Calculate distance between two GPS coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
