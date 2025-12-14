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
    // public function run()
    // {
    //     DB::table('attendances')->insert([
    //         [
    //             'user_id' => 1,
    //             'clock_in_time' => '2025-10-15 09:00:00',
    //             'break_start' => '2025-10-15 12:00:00',
    //             'break_end' => '2025-10-15 13:00:00',
    //             'clock_out_time' => '2025-10-15 18:00:00',

    //             'status' => 'editable',  // ← これが今の新しいデフォルト
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ],
    //         [
    //             'user_id' => 1,
    //             'clock_in_time' => '2025-10-15 08:50:00',
    //             'break_start' => '2025-10-15 12:10:00',
    //             'break_end' => '2025-10-15 13:00:00',
    //             'clock_out_time' => '2025-10-15 17:45:00',

    //             'status' => 'editable',  // ← これが今の新しいデフォルト
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ],
    //     ]);
    //     // ------------------------------------------------------------
    //     // ✅ ここから「30日分自動生成」ダミーデータの追加処理
    //     // ------------------------------------------------------------
    //     // $user = User::first(); // 例: 最初のユーザーに付与
    //     $users = User::all();

    //     foreach ($users as $user) {
    //         for ($i = 0; $i < 30; $i++) {
    //             $date = Carbon::now()->subDays($i);

    //             // ランダムに出退勤・休憩時間を生成
    //             $clockIn = $date->copy()->setTime(rand(8, 9), rand(0, 59));
    //             $breakStart = $clockIn->copy()->addHours(4);
    //             $breakEnd = $breakStart->copy()->addMinutes(rand(45, 60));
    //             $clockOut = $date->copy()->setTime(rand(17, 18), rand(0, 59));

    //             DB::table('attendances')->insert([
    //                 'user_id' => $user->id,
    //                 'clock_in_time' => $clockIn,
    //                 'break_start' => $breakStart,
    //                 'break_end' => $breakEnd,
    //                 'clock_out_time' => $clockOut,
    //                 'status' => 'editable',  // ← これが今の新しいデフォルト
    //                 'created_at' => $date,
    //                 'updated_at' => $date,
    //             ]);
    //         }
    //     }
    //     echo "✅ 30日分のダミーデータを追加しました。\n";
    // }


    public function run()
    {
        // まず2件の初期サンプルデータを挿入（休憩はrestsに後で追加）
        $attendance1_id = DB::table('attendances')->insertGetId([
            'user_id' => 1,
            'clock_in_time' => '2025-10-15 09:00:00',
            'clock_out_time' => '2025-10-15 18:00:00',
            // 'status' => 'editable',
            'status' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $attendance2_id = DB::table('attendances')->insertGetId([
            'user_id' => 1,
            'clock_in_time' => '2025-10-15 08:50:00',
            'clock_out_time' => '2025-10-15 17:45:00',
            // 'status' => 'editable',
            'status' => 'none',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // それぞれに対応する休憩データを rests テーブルへ挿入
        DB::table('rests')->insert([
            [
                'attendance_id' => $attendance1_id,
                'break_start' => '2025-10-15 12:00:00',
                'break_end' => '2025-10-15 13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => $attendance2_id,
                'break_start' => '2025-10-15 12:10:00',
                'break_end' => '2025-10-15 13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ------------------------------------------------------------
        // ✅ 以下は「30日分自動生成」ダミーデータの追加処理
        // ------------------------------------------------------------
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);

                // 出退勤時間
                $clockIn = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                $clockOut = $clockIn->copy()->addHours(9)->addMinutes(rand(0, 59));

                // attendances登録
                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => $user->id,
                    'clock_in_time' => $clockIn,
                    'clock_out_time' => $clockOut,
                    'status' => 'editable',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                // rests登録（休憩1時間想定）
                $breakStart = $clockIn->copy()->addHours(4);
                $breakEnd = $breakStart->copy()->addMinutes(rand(45, 60));

                DB::table('rests')->insert([
                    'attendance_id' => $attendanceId,
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        echo "✅ attendances・rests のダミーデータ生成が完了しました。\n";
    }
}
