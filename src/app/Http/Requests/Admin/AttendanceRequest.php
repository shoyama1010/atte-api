<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true; // 管理者は常に許可
    }

    public function rules()
    {
        return [
            // 出勤・退勤
            'clock_in_time'  => ['required', 'date_format:H:i'],
            'clock_out_time' => ['required', 'date_format:H:i', 'after:clock_in_time'],

            // 休憩時間
            'break_start' => ['nullable', 'date_format:H:i', 'before:break_end'],
            'break_end'   => ['nullable', 'date_format:H:i', 'after:break_start'],

            // 備考
            'remarks'        => ['required', 'string', 'max:255'],
            // 'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            // 出勤・退勤
            'clock_in_time.required'  => '出勤時刻を入力してください。',
            'clock_in_time.date_format' => '出勤時刻の形式が正しくありません。',
            'clock_out_time.required' => '退勤時刻を入力してください。',
            'clock_out_time.after'    => '休憩時間もしくは退勤時間が不適切な値です。',

            // 休憩
            'break_start.before' => '休憩開始時刻は休憩終了時刻より前に設定してください。',
            'break_end.after'    => '休憩終了時刻は休憩開始時刻より後である必要があります。',

            // 備考
            'remarks.required' => '備考を入力してください。',
            'remarks.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
