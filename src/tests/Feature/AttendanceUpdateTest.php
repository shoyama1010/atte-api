<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤時間が退勤時間より後の場合、
     * バリデーションエラーになる
     */
    public function test_start_time_after_end_time_returns_validation_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in_time' => '2026-08-30 09:00:00',
            'clock_out_time' => '2026-08-30 18:00:00',
        ]);

        $response = $this->actingAs($user)->put(
            '/attendance/update/' . $attendance->id,
            [
                'clock_in_time' => '18:00',
                'clock_out_time' => '09:00',
                'note' => 'テスト理由',
            ]
        );

        $response->assertSessionHasErrors([
            'clock_in_time',
        ]);
    }

    /**
     * 備考が空の場合、
     * バリデーションエラーになる
     */
    public function test_empty_note_returns_validation_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in_time' => '2026-08-30 09:00:00',
            'clock_out_time' => '2026-08-30 18:00:00',
        ]);

        $response = $this->actingAs($user)->put(
            '/attendance/update/' . $attendance->id,
            [
                'clock_in_time' => '09:00',
                'clock_out_time' => '18:00',
                'note' => '',
            ]
        );

        $response->assertSessionHasErrors([
            'note',
        ]);
    }

    /**
     * 正常なデータで勤怠を更新できる
     */
    public function test_valid_data_can_update_attendance()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in_time' => '2026-08-30 09:00:00',
            'clock_out_time' => '2026-08-30 17:00:00',
        ]);

        $response = $this->actingAs($user)->put(
            '/attendance/update/' . $attendance->id,
            [
                'clock_in_time' => '08:30',
                'clock_out_time' => '17:30',
                'note' => 'テスト更新',
            ]
        );

        // 更新後はリダイレクト
        $response->assertStatus(302);

        // 勤怠データが更新されたことを確認
        $attendance->refresh();

        $this->assertEquals(
            '08:30',
            $attendance->clock_in_time->format('H:i')
        );

        $this->assertEquals(
            '17:30',
            $attendance->clock_out_time->format('H:i')
        );
    }
}
