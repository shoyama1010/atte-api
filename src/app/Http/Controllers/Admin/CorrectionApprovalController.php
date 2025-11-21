<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // 申請データを取得
        $correction = CorrectionRequest::findOrFail($id);
        // 対象の勤怠データを取得
        $attendance = Attendance::findOrFail($correction->attendance_id);

        // 🔹 勤怠データを「after_～」の値で更新
        $attendance->update([
            'clock_in_time'  => $correction->after_clock_in,
            'clock_out_time' => $correction->after_clock_out,
            'break_start'    => $correction->after_break_start,
            'break_end'      => $correction->after_break_end,
        ]);

        // 🔹 申請データを「承認済み」に変更し、承認者IDを登録
        $correction->update([
            'status'   => 'approved',
            'admin_id' => Auth::guard('admin')->id(), // 管理者認証ガード使用
        ]);

        // 完了後、一覧に戻る
        return redirect()->route('admin.stamp_correction_request.list')
            ->with('success', '修正申請を承認し、勤怠情報を更新しました。');

    }
}
