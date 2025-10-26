<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequestFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in_time.required' => '出勤時刻を入力してください。',
            'clock_out_time.required' => '退勤時刻を入力してください。',
            'clock_out_time.after' => '退勤時刻は出勤時刻より後の時間にしてください。',
            'break_start.after_or_equal' => '休憩開始は勤務時間内に設定してください。',
            'break_start.before' => '休憩開始は退勤時刻より前に設定してください。',
            'break_end.after' => '休憩終了は休憩開始より後に設定してください。',
            'break_end.before' => '休憩終了は勤務時間内に設定してください。',
        ];
    }
}
