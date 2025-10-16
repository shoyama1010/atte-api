<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorrectionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('correction_requests')->insert([
            [
                'attendance_id' => 2,
                'user_id' => 2,
                'admin_id' => 1,
                 'request_type' => 'time_change', // ✅ これを追加
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 2,
                'user_id' => 2,
                'admin_id' => 1,
                'request_type' => 'approval', // ✅ これも追加
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
