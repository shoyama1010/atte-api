<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class AttendanceValidationTest extends TestCase
{
    public function test_clock_in_after_out_fails_validation()
    {
        $data = [
            'clock_in_time' => '18:00',
            'clock_out_time' => '09:00',
        ];

        $rules = [
            'clock_in_time' => 'required|before:clock_out_time',
            'clock_out_time' => 'required',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'clock_in_time',
            $validator->errors()->toArray()
        );
    }
}
