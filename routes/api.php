<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OfficeController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\AttendanceController;

Route::get('/health', function () {
    return response()->json(['status' => 'API is running']);
});

// Public routes (without authentication for now)
Route::apiResource('offices', OfficeController::class);
Route::apiResource('schedules', ScheduleController::class);
Route::apiResource('attendances', AttendanceController::class);

// User-specific routes
Route::get('/users/{userId}/schedules', [ScheduleController::class, 'index']);
Route::get('/users/{userId}/attendances', [AttendanceController::class, 'index']);

// Reports
Route::get('/reports/monthly', function (Request $request) {
    $month = $request->query('month');
    $year = $request->query('year');
    $officeId = $request->query('office_id');
    
    $attendances = \App\Models\Attendance::whereMonth('work_date', $month)
        ->whereYear('work_date', $year);
    
    if ($officeId) {
        $attendances->where('office_id', $officeId);
    }
    
    $data = $attendances->with(['user', 'office'])->get();
    
    $summary = [
        'total_employees' => $data->pluck('user_id')->unique()->count(),
        'present_count' => $data->where('status', 'present')->count(),
        'late_count' => $data->where('status', 'late')->count(),
        'absent_count' => $data->where('status', 'absent')->count(),
        'half_day_count' => $data->where('status', 'half-day')->count(),
    ];
    
    return response()->json([
        'success' => true,
        'data' => [
            'month' => \Carbon\Carbon::create($year, $month)->format('F Y'),
            'office_id' => $officeId,
            'summary' => $summary,
            'attendances' => $data,
        ]
    ]);
});
