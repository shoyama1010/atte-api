<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Models\Attendance;
use App\Models\Rest;
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

            $attendance = $requestData->attendance;

        return view('admin.stamp_correction_request.approve', compact('requestData', 'attendance'));
    }

    /**
     * 承認ボタン押下処理
     */
    public function approve(Request $request, $id)
    {
        // 申請データを取得
        $correction = CorrectionRequest::findOrFail($id);

        // 対象の勤怠データを取得（休憩含む）
        $attendance = Attendance::with('rests')->findOrFail($correction->attendance_id);

        // ▼ 勤怠の基本項目を更新
        $attendance->update([
            'clock_in_time'  => $correction->after_clock_in,
            'clock_out_time' => $correction->after_clock_out,
            'note'           => $correction->reason,
        ]);
        // $attendance->rests()->delete(); // rests は「既に一般側で修正済み」として使う
        // ▼ 新しい休憩データを再登録
        // correction_requests には休憩情報を保持しない構成なので、
        // 対象 attendance_id の rests テーブルに登録済みの内容を再利用
        $existingRests = Rest::where('attendance_id', $attendance->id)->get();

        // // ▼ 申請データのステータスを「承認済み」に更新
        // ※ ここをコメントアウトすると画面遷移だけ行われてDB反映されなくなるので注意！
        $correction->update([
            'status'   => 'approved',
            'admin_id' => Auth::guard('admin')->id(),
        ]);

        // ▼ 完了メッセージを返す
        return redirect()
            ->route('admin.stamp_correction_request.list')
            ->with('success', '修正申請を承認し、勤怠情報を更新しました。');
    }
}
