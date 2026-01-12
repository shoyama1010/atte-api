<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Validator; // ← Validatorを使うために必要

class AttendanceValidationTest extends TestCase
{
    /**
     * 出勤時間が退勤時間より後の場合のバリデーションエラーテスト
     *
     * @return void
     */

    public function test_clock_in_after_out_fails_validation()
    {
        // テストデータ（出勤 > 退勤 の不正パターン）
         $data = [
            'clock_in_time' => '18:00',
            'clock_out_time' => '09:00'
        ];

        // バリデーションルール
        $rules = [
            'clock_in_time' => 'required|before:clock_out_time',
            'clock_out_time' => 'required'
        ];
        // Validatorを実行
        $validator = Validator::make($data, $rules);

        // 結果の検証（失敗しているはず）
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('clock_in_time', $validator->errors()->toArray());
    }
}

