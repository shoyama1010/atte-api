<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class CorrectionRequestController extends Controller
{
    /**
     * 勤務修正申請フォームを表示
     */
    public function edit($attendanceId)
    {
        $user = Auth::user();
        $attendance = Attendance::where('id', $attendanceId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('attendance.request', compact('user', 'attendance'));
    }

    /**
     * 勤務修正申請を送信
     */
    public function update(Request $request, $attendanceId)
    {
        $request->validate([
            'clock_in_time' => 'required|date_format:H:i',
            'clock_out_time' => 'required|date_format:H:i|after:clock_in_time',
            'break_start' => 'nullable|date_format:H:i|after:clock_in_time|before:clock_out_time',
            'break_end' => 'nullable|date_format:H:i|after:break_start|before:clock_out_time',
            'reason' => 'required|string|max:255',
        ], [
            'clock_in_time.required' => '出勤時間を入力してください。',
            'clock_out_time.required' => '退勤時間を入力してください。',
            'clock_out_time.after' => '退勤時間は出勤時間より後にしてください。',
            'break_start.before' => '休憩開始は退勤時間より前にしてください。',
            'break_end.after' => '休憩終了は休憩開始より後にしてください。',
            'reason.required' => '修正理由を入力してください。',
        ]);

        CorrectionRequest::create([
            'attendance_id' => $attendanceId,
            'user_id' => Auth::id(),
            'admin_id' => null,
            'request_type' => 'time_change',
            'reason' => $request->reason,
            'status' => 'pending', // 未承認
        ]);

        return redirect()->route('attendance.detail', $attendanceId)
            ->with('success', '修正申請を送信しました。');
    }
}
