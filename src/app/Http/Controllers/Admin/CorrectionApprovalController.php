<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CorrectionApprovalController extends Controller
{
    // 修正申請の詳細画面（承認前・承認後共通）
    public function show($id)
    {
        //修正申請データー（勤怠＋休憩＋ユーザー）
        $requestData = CorrectionRequest::with([
            'attendance',
            'attendance.rests',
            'attendance.user'
            ])->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('requestData'));
    }
    /**
     * 承認ボタン押下処理
     */
    public function approve(Request $request, $id)
    {
        // 申請データ
        $correction = CorrectionRequest::findOrFail($id);
        // 対象の勤怠
        $attendance = Attendance::with('rests')->findOrFail($correction->attendance_id);
        // ▼ 1. 勤怠の基本項目を更新
        $attendance->update([
            'clock_in_time'  => $correction->after_clock_in,
            'clock_out_time' => $correction->after_clock_out,
            'note'           => $correction->reason,
        ]);
        // ▼ 2. 既存の休憩データを削除
        $attendance->rests()->delete();
        // ▼ 3. after の休憩が存在する場合のみ登録
        if ($correction->after_break_start && $correction->after_break_end) {
            $attendance->rests()->create([
                'break_start' => $correction->after_break_start,
                'break_end'   => $correction->after_break_end,
            ]);
        }
        // ▼ 4. 申請データのステータス更新
        $correction->update([
            'status'   => 'approved',
            'admin_id' => Auth::guard('admin')->id(),
        ]);
        return redirect()
            ->route('admin.stamp_correction_request.list')
            ->with('success', '修正申請を承認し、勤怠情報を更新しました。');
    }
}
