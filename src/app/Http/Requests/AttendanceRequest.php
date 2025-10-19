<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return false;
        return true; // ログイン済みであれば true
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in_time'  => 'nullable|date',
            'clock_out_time' => 'nullable|date|after:clock_in_time',
            'status'         => 'required|in:出勤前,勤務中,休憩中,退勤済み',
        ];
    }

    public function messages()
    {
        return [
            'clock_out_time.after' => '退勤時刻は出勤時刻より後である必要があります。',
        ];
    }
}
