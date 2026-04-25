<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    // Ethiopian work shifts converted to standard time (ET clock - 6 hours)
    // Morning:   ET 2:00–6:30  → Standard 08:00–12:30
    // Afternoon: ET 7:30–11:30 → Standard 13:30–17:30
    const SHIFTS = [
        ['start_time' => '08:00:00', 'end_time' => '12:30:00'],
        ['start_time' => '13:30:00', 'end_time' => '17:30:00'],
    ];

    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function run(): void
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            foreach (self::DAYS as $day) {
                foreach (self::SHIFTS as $shift) {
                    Schedule::firstOrCreate(
                        [
                            'user_id'    => $user->id,
                            'day_of_week'=> $day,
                            'start_time' => $shift['start_time'],
                        ],
                        ['end_time' => $shift['end_time']]
                    );
                }
            }
        }
    }
}
