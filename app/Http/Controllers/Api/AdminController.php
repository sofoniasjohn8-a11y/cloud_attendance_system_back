<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AbsentNotification;
use App\Models\Attendance;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    // List all users
    public function users()
    {
        $users = User::where('role', 'user')
            ->withCount('attendances')
            ->get();

        return response()->json(['success' => true, 'data' => $users]);
    }

    // Get a single user with their attendance history
    public function showUser($id)
    {
        $user = User::with(['attendances' => fn($q) => $q->orderBy('work_date', 'desc')])->find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $user]);
    }

    // Update a user's role (promote/demote)
    public function updateUserRole(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $validated = $request->validate(['role' => 'required|in:admin,user']);
        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'Role updated', 'data' => $user]);
    }

    // Delete a user
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted']);
    }

    // Admin override: edit clock-in / clock-out of any attendance record
    public function updateAttendance(Request $request, $id)
    {
        try {
            $attendance = Attendance::with(['user', 'office'])->find($id);

            if (!$attendance) {
                return response()->json(['success' => false, 'message' => 'Attendance record not found'], 404);
            }

            $validated = $request->validate([
                'clock_in'  => 'nullable|date_format:Y-m-d H:i:s',
                'clock_out' => 'nullable|date_format:Y-m-d H:i:s|after:clock_in',
                'status'    => 'nullable|in:present,late,absent,half-day',
                'remarks'   => 'nullable|string',
            ]);

            $attendance->update(array_filter($validated, fn($v) => !is_null($v)));

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated',
                'data'    => $attendance->fresh(['user', 'office']),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    // Send absent email to a specific user for a given date
    public function sendAbsentEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'date'    => 'required|date_format:Y-m-d',
            ]);

            $user = User::find($validated['user_id']);
            Mail::to($user->email)->send(new AbsentNotification($user, $validated['date']));

            Notification::create([
                'user_id' => $user->id,
                'type'    => 'absent',
                'title'   => 'Absence Recorded',
                'message' => "You were marked absent on {$validated['date']}. Please contact HR if this is incorrect.",
                'date'    => $validated['date'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "Absent notification sent to {$user->email} for {$validated['date']}",
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }

    // Send absent emails to ALL users who have no attendance record for a given date
    public function sendAbsentEmailsForDate(Request $request)
    {
        $validated = $request->validate(['date' => 'required|date_format:Y-m-d']);

        $presentUserIds = Attendance::whereDate('work_date', $validated['date'])
            ->whereIn('status', ['present', 'late', 'half-day'])
            ->pluck('user_id');

        $absentUsers = User::where('role', 'user')
            ->whereNotIn('id', $presentUserIds)
            ->get();

        foreach ($absentUsers as $user) {
            Mail::to($user->email)->send(new AbsentNotification($user, $validated['date']));
            Notification::create([
                'user_id' => $user->id,
                'type'    => 'absent',
                'title'   => 'Absence Recorded',
                'message' => "You were marked absent on {$validated['date']}. Please contact HR if this is incorrect.",
                'date'    => $validated['date'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Absent notifications sent to {$absentUsers->count()} user(s)",
            'notified' => $absentUsers->pluck('email'),
        ]);
    }

    // Daily attendance overview — grouped by schedule slot
    public function dailyOverview(Request $request)
    {
        $date      = $request->query('date', now()->toDateString());
        $officeId  = $request->query('office_id');
        $dayOfWeek = Carbon::parse($date)->format('l');

        // All users
        $allUsers = User::where('role', 'user')->get();

        // All schedules for this day
        $schedules = Schedule::with('user')
            ->where('day_of_week', $dayOfWeek)
            ->whereIn('user_id', $allUsers->pluck('id'))
            ->get();

        // All attendances for this date
        $attendanceQuery = Attendance::with(['user', 'office', 'schedule'])
            ->whereDate('work_date', $date);
        if ($officeId) $attendanceQuery->where('office_id', $officeId);
        $attendances = $attendanceQuery->get()->keyBy(fn($a) => $a->user_id . '_' . $a->schedule_id);

        // Build per-schedule breakdown
        $scheduleBreakdown = $schedules->groupBy('id')->map(function ($group) use ($attendances, $allUsers) {
            $schedule     = $group->first();
            $key          = $schedule->user_id . '_' . $schedule->id;
            $attendance   = $attendances->get($key);
            $user         = $allUsers->firstWhere('id', $schedule->user_id);

            return [
                'schedule_id'  => $schedule->id,
                'day_of_week'  => $schedule->day_of_week,
                'start_time'   => $schedule->start_time,
                'end_time'     => $schedule->end_time,
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'status'       => $attendance ? $attendance->status : 'absent',
                'clock_in'     => $attendance?->clock_in,
                'clock_out'    => $attendance?->clock_out,
                'office'       => $attendance?->office?->name,
                'attendance_id'=> $attendance?->id,
            ];
        })->values();

        // Summary arrays for frontend cards
        $present = $scheduleBreakdown->whereIn('status', ['present', 'late', 'half-day'])->values();
        $absent  = $scheduleBreakdown->where('status', 'absent')->values();

        // Office breakdown
        $officeBreakdown = $attendances->groupBy(fn($a) => $a->office_id)
            ->map(fn($group) => [
                'office_id'   => $group->first()->office_id,
                'office_name' => $group->first()->office?->name,
                'total'       => $group->count(),
                'present'     => $group->whereIn('status', ['present', 'late', 'half-day'])->count(),
                'late'        => $group->where('status', 'late')->count(),
            ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'date'               => $date,
                'day_of_week'        => $dayOfWeek,
                'total_users'        => $allUsers->count(),
                'present'            => $present,
                'absent'             => $absent,
                'schedule_breakdown' => $scheduleBreakdown,
                'office_breakdown'   => $officeBreakdown,
            ],
        ]);
    }

    // Calendar overview — for a given month, return per-day per-schedule status
    public function calendarOverview(Request $request)
    {
        $month    = $request->query('month', now()->month);
        $year     = $request->query('year', now()->year);
        $officeId = $request->query('office_id');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();
        $allUsers  = User::where('role', 'user')->get();

        $attendanceQuery = Attendance::with(['user', 'schedule', 'office'])
            ->whereBetween('work_date', [$startDate, $endDate]);
        if ($officeId) $attendanceQuery->where('office_id', $officeId);
        $attendances = $attendanceQuery->get();

        // Group by date
        $byDate = $attendances->groupBy(fn($a) => $a->work_date->toDateString())
            ->map(function ($dayAttendances, $date) use ($allUsers) {
                $dayOfWeek = Carbon::parse($date)->format('l');

                $scheduledUsers = Schedule::where('day_of_week', $dayOfWeek)
                    ->whereIn('user_id', $allUsers->pluck('id'))
                    ->get();

                $presentIds = $dayAttendances->pluck('user_id');

                return [
                    'date'        => $date,
                    'day_of_week' => $dayOfWeek,
                    'scheduled'   => $scheduledUsers->count(),
                    'present'     => $dayAttendances->whereIn('status', ['present', 'late', 'half-day'])->count(),
                    'absent'      => $scheduledUsers->whereNotIn('user_id', $presentIds)->count(),
                    'late'        => $dayAttendances->where('status', 'late')->count(),
                    'slots'       => $dayAttendances->map(fn($a) => [
                        'user_id'     => $a->user_id,
                        'user_name'   => $a->user?->name,
                        'schedule_id' => $a->schedule_id,
                        'start_time'  => $a->schedule?->start_time,
                        'end_time'    => $a->schedule?->end_time,
                        'status'      => $a->status,
                        'clock_in'    => $a->clock_in,
                        'clock_out'   => $a->clock_out,
                    ])->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'month'  => $startDate->format('F Y'),
                'days'   => $byDate->values(),
            ],
        ]);
    }
}
