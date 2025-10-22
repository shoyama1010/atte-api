<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

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
                // 'status' => 'working', // ✅ ENUMに合わせて文字列に修正
                'status' => 'none', // ← ✅ ENUMに合わせて修正！
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'clock_in_time' => '2025-10-15 08:50:00',
                'break_start' => '2025-10-15 12:10:00',
                'break_end' => '2025-10-15 13:00:00',
                'clock_out_time' => '2025-10-15 17:45:00',
                // 'status' => 1,
                'status' => 'none', // ✅ ENUM型文字列
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ------------------------------------------------------------
        // ✅ ここから「30日分自動生成」ダミーデータの追加処理
        // ------------------------------------------------------------

        // $user = User::first(); // 例: 最初のユーザーに付与
        $users = User::all();
        // if (!$user) {
        //     echo "⚠️ Userが存在しません。UserSeederを実行してください。\n";
        //     return;
        // }
        foreach ($users as $user) {
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);

                // ランダムに出退勤・休憩時間を生成
                $clockIn = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                $breakStart = $clockIn->copy()->addHours(4);
                $breakEnd = $breakStart->copy()->addMinutes(rand(45, 60));
                $clockOut = $date->copy()->setTime(rand(17, 18), rand(0, 59));

                DB::table('attendances')->insert([
                    'user_id' => $user->id,
                    'clock_in_time' => $clockIn,
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                    'clock_out_time' => $clockOut,
                    // 'status' => 'off_duty',
                    'status' => 'none', // ← ✅ ENUMに合わせて修正！
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
        echo "✅ 30日分のダミーデータを追加しました。\n";
    }
}
