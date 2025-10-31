<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Http\Requests\CorrectionRequestFormRequest;

class CorrectionRequestController extends Controller
{
    /**
     * 勤務修正申請一覧の表示
     */
    public function list()
    {
        // $user = Auth::user();
        $status = request('status', 'pending');
        // ログインユーザー自身の申請履歴を新しい順に取得
        $requests = CorrectionRequest::where('user_id', auth()->id())
            ->with('attendance')
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.list', compact('requests'));
    }
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
    public function update(CorrectionRequestFormRequest $request, $attendanceId)
    {
        $user = Auth::user();
        $attendance = Attendance::findOrFail($attendanceId);

        // 修正申請を新規登録
        CorrectionRequest::create([
            'attendance_id'   => $attendance->id,
            'user_id'         => $user->id,
            'admin_id'        => null,
            'request_type'            => 'time_change',
            'reason'          => $request->reason,
            // 'before_clock_in' => $attendance->clock_in_time,
            // 'before_clock_out' => $attendance->clock_out_time,
            // 'before_break_start' => $attendance->break_start,
            // 'before_break_end'   => $attendance->break_end,
            // 'after_clock_in'  => $request->clock_in_time,
            // 'after_clock_out' => $request->clock_out_time,
            // 'after_break_start' => $request->break_start,
            // 'after_break_end'   => $request->break_end,
            // 🔹 修正前（before系）＝元の勤怠データから取得
            'before_clock_in'    => $attendance->clock_in_time,
            'before_clock_out'   => $attendance->clock_out_time,
            'before_break_start' => $attendance->break_start,
            'before_break_end'   => $attendance->break_end,

            // 🔹 修正後（after系）＝フォームの入力値
            'after_clock_in'    => $request->clock_in_time,
            'after_clock_out'   => $request->clock_out_time,
            'after_break_start' => $request->break_start,
            'after_break_end'   => $request->break_end,
            'status'          => 'pending',
        ]);

        // return redirect()->route('attendance.detail', $attendanceId)
        //     ->with('success', '修正申請を送信しました。');

        return redirect()->route('stamp_correction_request.list')
            ->with('success', '修正申請を送信しました。');
    }
}
