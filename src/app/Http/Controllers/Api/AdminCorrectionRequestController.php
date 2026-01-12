<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminCorrectionRequestController extends Controller
{
    public function show($id)
    {
        $requestData = CorrectionRequest::with(['attendance.rests', 'user'])
            ->findOrFail($id);

        $attendance = $requestData->attendance;
        $rest = $attendance->rests->first();

        // 安全に時間を H:i に変換するヘルパー
        $toHM = function ($time) {
            if (!$time) return null;
            return Carbon::parse($time)->format('H:i');
        };

        return response()->json([
            'id' => $requestData->id,
            'user_name' => $requestData->user->name,
            'target_date' => Carbon::parse($attendance->clock_in_time)->format('Y-m-d'),
            'reason' => $requestData->reason,

            // 修正前（元の勤怠）
            'before_clock_in'  => $toHM($attendance->clock_in_time),
            'before_clock_out' => $toHM($attendance->clock_out_time),
            'before_break_start' => $toHM($rest?->break_start),
            'before_break_end'   => $toHM($rest?->break_end),

            // 修正後（管理者が確認する値）
            'after_clock_in'  => $toHM($requestData->after_clock_in),
            'after_clock_out' => $toHM($requestData->after_clock_out),
            'after_break_start' => $toHM($requestData->after_break_start),
            'after_break_end'   => $toHM($requestData->after_break_end),
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
