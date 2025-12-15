<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\CorrectionRequest;
use App\Http\Requests\CorrectionRequestFormRequest;
use Carbon\Carbon;

class CorrectionRequestController extends Controller
{
    /**
     * 勤務修正申請一覧の表示
     */
    public function list()
    {
        $user = Auth::user();
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
        $attendance = Attendance::with('rests')->findOrFail($attendanceId);

        return view('attendance.request', compact('user', 'attendance'));
    }
    
    /**
     * 勤務修正申請を送信
     */
    public function update(CorrectionRequestFormRequest $request, $attendanceId)
    {
        $user = Auth::user();
        $attendance = Attendance::with('rests')->findOrFail($attendanceId);
        // 休憩は1件想定
        $rest = $attendance->rests->first();

        // ▼ フォームの after（修正後）休憩
        $afterBreakStart = $request->input('rests.0.break_start');
        $afterBreakEnd   = $request->input('rests.0.break_end');

        CorrectionRequest::create([
            'attendance_id'   => $attendance->id,
            'user_id'         => $user->id,
            'admin_id'        => null,
            'request_type'    => 'time_change',
            // 'reason'          => $request->reason,
            'reason'          => $request->note,

            // Before（元の勤怠）
            'before_clock_in'    => $attendance->clock_in_time,
            'before_clock_out'   => $attendance->clock_out_time,
            'before_break_start' => optional($rest)->break_start,
            'before_break_end'   => optional($rest)->break_end,
            // After（修正後、フォーム値）
            'after_clock_in'    => $request->clock_in_time,
            'after_clock_out'   => $request->clock_out_time,
            'after_break_start' => $afterBreakStart,
            'after_break_end'   => $afterBreakEnd,
            'status'          => 'pending',
        ]);

        return redirect()->route('stamp_correction_request.list')
            ->with('success', '修正申請を送信しました。');
    }

    public function show($id)
    {
        // 修正申請データ + 勤務データ + 休憩データ + ユーザー
        $requestData = CorrectionRequest::with([
            'attendance',
            'attendance.rests',
            'attendance.user',
            'user'
        ])->findOrFail($id);
        return view('admin.stamp_correction_request.approve', compact('requestData'));
    }
}
