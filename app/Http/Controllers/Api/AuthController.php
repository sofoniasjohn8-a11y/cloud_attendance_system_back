<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|string|min:6',
                'office_id' => 'nullable|exists:offices,id',
            ]);

            // Auto-assign first office if none provided
            $officeId = $validated['office_id'] ?? Office::first()?->id;

            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => bcrypt($validated['password']),
                'office_id' => $officeId,
            ]);

            // Auto-assign Ethiopian work schedules
            $this->assignDefaultSchedules($user);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'token'   => $token,
                'user'    => $user->load('office'),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    private function assignDefaultSchedules(User $user): void
    {
        $days   = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $shifts = [
            ['start_time' => '08:00:00', 'end_time' => '12:30:00'],
            ['start_time' => '13:30:00', 'end_time' => '17:30:00'],
        ];

        foreach ($days as $day) {
            foreach ($shifts as $shift) {
                Schedule::firstOrCreate(
                    ['user_id' => $user->id, 'day_of_week' => $day, 'start_time' => $shift['start_time']],
                    ['end_time' => $shift['end_time']]
                );
            }
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password',
                ], 401);
            }

            // Revoke old tokens and issue a fresh one
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token'   => $token,
                'user'    => $user->load('office'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user()->load('office'),
        ]);
    }
}
