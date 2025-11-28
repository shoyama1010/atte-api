<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;

class AdminCorrectionRequestController extends Controller
{
    public function show($id)
    {
        $requestData = CorrectionRequest::with(['attendance.rests', 'user'])
            ->findOrFail($id);

        return response()->json([
            'id' => $requestData->id,
            'user_name' => $requestData->user->name,
            'request_date' => $requestData->created_at->format('Y-m-d'),
            'target_date' => $requestData->attendance->created_at->format('Y-m-d'),
            'reason' => $requestData->reason,

            // before
            'before_clock_in'  => optional($requestData->before_clock_in)->format('H:i'),
            'before_clock_out' => optional($requestData->before_clock_out)->format('H:i'),
            'before_break_start' => optional($requestData->before_break_start)->format('H:i'),
            'before_break_end'   => optional($requestData->before_break_end)->format('H:i'),

            // after 修正後
            'after_clock_in'  => optional($requestData->after_clock_in)->format('H:i'),
            'after_clock_out' => optional($requestData->after_clock_out)->format('H:i'),
            'after_break_start' => optional($requestData->after_break_start)->format('H:i'),
            'after_break_end'   => optional($requestData->after_break_end)->format('H:i'),
        ]);
    }

    public function approve($id)
    {
        $correction = CorrectionRequest::with('attendance')->findOrFail($id);
        $attendance = $correction->attendance;

        // 勤怠の値を "after_〜" で更新
        $attendance->update([
            'clock_in_time'  => $correction->after_clock_in,
            'clock_out_time' => $correction->after_clock_out,
        ]);

        // 休憩（rests テーブル）更新
        if ($correction->after_break_start && $correction->after_break_end) {
            $attendance->rests()->delete(); // 既存休憩削除

            $attendance->rests()->create([
                'break_start' => $correction->after_break_start,
                'break_end'   => $correction->after_break_end,
            ]);
        }

        // 申請ステータス更新
        $correction->update([
            'status' => 'approved',
            'admin_id' => auth('admin')->id(),
        ]);

        return response()->json([
            'message' => '承認しました',
            'attendance_id' => $attendance->id,
            'correction_id' => $correction->id,
        ]);
    }
}
