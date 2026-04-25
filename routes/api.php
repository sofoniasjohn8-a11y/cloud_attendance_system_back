<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OfficeController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\AttendanceController;

Route::get('/health', function () {
    return response()->json(['status' => 'API is running']);
});

// TEMPORARY - remove after use
Route::get('/make-admin/{email}', function ($email) {
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) return response()->json(['error' => 'User not found'], 404);
    $user->update(['role' => 'admin']);
    return response()->json(['success' => true, 'message' => $email . ' is now admin']);
});

// Auth routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Public read-only office list (needed by frontend before login)
Route::get('/offices', [OfficeController::class, 'index']);
Route::get('/offices/{office}', [OfficeController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::apiResource('offices', OfficeController::class)->except(['index', 'show']);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('attendances', AttendanceController::class);

    Route::get('/users/{userId}/schedules',   [ScheduleController::class,  'index']);
    Route::get('/users/{userId}/attendances', [AttendanceController::class, 'index']);

    // Notification routes
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read',    [NotificationController::class, 'markRead']);
    Route::put('/notifications/read-all',     [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{id}',      [NotificationController::class, 'destroy']);

    // Admin-only routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users',                          [AdminController::class, 'users']);
        Route::get('/users/{id}',                     [AdminController::class, 'showUser']);
        Route::put('/users/{id}/role',                [AdminController::class, 'updateUserRole']);
        Route::delete('/users/{id}',                  [AdminController::class, 'deleteUser']);
        Route::put('/attendances/{id}',               [AdminController::class, 'updateAttendance']);
        Route::get('/overview',                       [AdminController::class, 'dailyOverview']);
        Route::get('/calendar',                       [AdminController::class, 'calendarOverview']);
        Route::post('/notify/absent',                 [AdminController::class, 'sendAbsentEmail']);
        Route::post('/notify/absent-all',             [AdminController::class, 'sendAbsentEmailsForDate']);
    });
});

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
