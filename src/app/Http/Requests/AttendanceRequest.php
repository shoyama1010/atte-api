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
            // 出勤・退勤
            'clock_in_time'  => 'nullable',
            'date_format:H:i',
            'before:clock_out_time',
            'clock_out_time' => 'nullable',
            'date_format:H:i',
            // 休憩
            'rests.*.break_start' => 'nullable',
            'date_format:H:i',
            'before_or_equal:clock_out_time',
            'rests.*.break_end'   => 'nullable',
            'date_format:H:i',
            'before_or_equal:clock_out_time',
            // 備考
            'note'           => 'required',
            'string',
            'max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // 出勤 > 退勤
            if (!empty($data['clock_in_time']) && !empty($data['clock_out_time'])) {
                if ($data['clock_in_time'] > $data['clock_out_time']) {
                    $validator->errors()->add('clock_in_time', '出勤時間が不適切な値です。');
                }
            }

            // 休憩 > 退勤
            if (!empty($data['rests'])) {
                foreach ($data['rests'] as $index => $rest) {
                    if (!empty($rest['break_start']) && !empty($data['clock_out_time'])) {
                        if ($rest['break_start'] > $data['clock_out_time']) {
                            $validator->errors()->add("rests.$index.break_start", '休憩時間が不適切な値です。');
                        }
                    }
                    if (!empty($rest['break_end']) && !empty($data['clock_out_time'])) {
                        if ($rest['break_end'] > $data['clock_out_time']) {
                            $validator->errors()->add("rests.$index.break_end", '休憩時間もしくは退勤時間が不適切な値です。');
                        }
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            // --- 出勤・退勤 ---
            'clock_in_time.before' => '出勤時間が不適切な値です。',
            'clock_in_time.date_format'   => '出勤時間の形式が不正です。',
            'clock_out_time.date_format'  => '退勤時間の形式が不正です。',

            // --- 休憩 ---
            'rests.*.break_start.before_or_equal' => '休憩時間が不適切な値です。',
            'rests.*.break_end.before_or_equal'   => '休憩時間もしくは退勤時間が不適切な値です。',
            'rests.*.break_start.date_format' => '休憩開始時間の形式が不正です。',
            'rests.*.break_end.date_format'   => '休憩終了時間の形式が不正です。',

            // --- 備考 ---
            'note.required' => '備考を記入してください。',
            'note.string'   => '備考は文字列で入力してください。',
            'note.max'      => '備考は255文字以内で入力してください。',
        ];
    }
}
