<?php

// namespace Tests\Unit;
namespace Tests\Feature;

use Tests\TestCase; // LaravelのTestCaseを使用する
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;
    /**
     * 出勤時間が退勤時間より後の場合のバリデーションテスト
     */
    public function test_start_time_after_end_time_returns_validation_error()
    {
        // テストユーザー作成
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/attendance/update', [
            'clock_in_time'  => '18:00',
            'clock_out_time' => '09:00',
            'reason'         => 'テスト理由',
        ]);

        // バリデーションエラー（出勤時間）を検証
        $response->assertSessionHasErrors(['clock_in_time']);
    }

    /**
     * 修正理由が空の場合のバリデーションテスト
     */
    public function test_empty_reason_returns_validation_error()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/attendance/update', [
            'clock_in_time'  => '09:00',
            'clock_out_time' => '18:00',
            'reason'         => '',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    /**
     * 正常なデータで更新できることを確認
     */
    public function test_valid_data_can_update_attendance()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_time' => '09:00',
            'clock_out_time' => '17:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/update', [
            'clock_in_time'  => '08:30',
            'clock_out_time' => '17:30',
            'reason'         => 'テスト更新',
        ]);

        $response->assertStatus(302); // リダイレクト確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in_time' => '08:30',
        ]);
    }
}
