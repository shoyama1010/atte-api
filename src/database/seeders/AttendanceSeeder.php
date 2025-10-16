<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('attendances')->insert([
            [
                'user_id' => 1,
                'clock_in_time' => '2025-10-15 09:00:00',
                'break_start' => '2025-10-15 12:00:00',
                'break_end' => '2025-10-15 13:00:00',
                'clock_out_time' => '2025-10-15 18:00:00',
                // 'status' => 0,
                'status' => 'working', // ✅ ENUMに合わせて文字列に修正
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'clock_in_time' => '2025-10-15 08:50:00',
                'break_start' => '2025-10-15 12:10:00',
                'break_end' => '2025-10-15 13:00:00',
                'clock_out_time' => '2025-10-15 17:45:00',
                // 'status' => 1,
                'status' => 'off_duty', // ✅ ENUM型文字列
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
