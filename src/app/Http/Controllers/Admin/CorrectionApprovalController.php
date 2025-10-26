<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;

class CorrectionApprovalController extends Controller
{
    /**
     * 修正申請の詳細画面（承認前・承認後共通）
     */
    public function show($id)
    {
        $requestData = CorrectionRequest::with(['attendance.user'])
            ->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('requestData'));
    }

    /**
     * 承認ボタン押下処理
     */
    public function approve(Request $request, $id)
    {
        $correction = CorrectionRequest::findOrFail($id);
        $attendance = Attendance::findOrFail($correction->attendance_id);

        // attendances テーブルに反映
        $before = [
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
            'break_start' => $attendance->break_start,
            'break_end' => $attendance->break_end,
        ];

        // after_time の内容を取得（ユーザー申請時に JSON 保存されている前提）
        $after = json_decode($correction->after_time, true);

        // 勤怠情報を上書き更新
        $attendance->update([
            'clock_in_time' => $after['clock_in_time'] ?? $attendance->clock_in_time,
            'clock_out_time' => $after['clock_out_time'] ?? $attendance->clock_out_time,
            'break_start' => $after['break_start'] ?? $attendance->break_start,
            'break_end' => $after['break_end'] ?? $attendance->break_end,
        ]);

        // correction_requests のステータス更新
        $correction->update([
            'status' => 'approved',
            'admin_id' => auth()->id(),
            'before_time' => json_encode($before),
        ]);

        return redirect()->route('admin.correction_request.show', $id)
            ->with('success', '申請を承認しました。');
    }
}
