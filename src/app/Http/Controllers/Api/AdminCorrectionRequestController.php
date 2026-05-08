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

        // 安全に時間を H:i に変換するヘルパー
        $toHM = function ($time) {
            if (!$time) return null;
            return \Carbon\Carbon::parse($time)->format('H:i');
        };

        return response()->json([
            'id' => $requestData->id,
            'user_name' => $requestData->user->name,

            'target_date' => \Carbon\Carbon::parse($requestData->created_at)->format('Y-m-d'),
            'reason' => $requestData->reason,

            // 修正前（元の勤怠）
            'before_clock_in'  => $toHM($requestData->clock_in_time),
            'before_clock_out' => $toHM($requestData->clock_out_time),

            // 🔥 （配列で返す）
            'before_rests' => $attendance->rests->map(function ($rest) use ($toHM) {
                return [
                    'break_start' => $toHM($rest->break_start),
                    'break_end'   => $toHM($rest->break_end),
                ];
            })->toArray(),

            // 修正後（管理者が確認する値）
            'after_clock_in'  => $toHM($requestData->after_clock_in),
            'after_clock_out' => $toHM($requestData->after_clock_out),

            // 🔥 ここを統一
            'rests' => (
                $requestData->after_break_start && $requestData->after_break_end
            )
                ? [[
                    'break_start' => $toHM($requestData->after_break_start),
                    'break_end'   => $toHM($requestData->after_break_end),
                ]]
                : [],

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
            // 'admin_id' => auth('admin')->id(),
            'admin_id' => 1, // ←一旦これでOK
        ]);

        return response()->json([
            'message' => '承認しました',
            'attendance_id' => $attendance->id,
            'correction_id' => $correction->id,
        ]);
    }
}
