<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    // 勤務一覧 API
    public function index()
    {
        $records = Attendance::with(['user', 'rests'])   // ← rests を追加
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'user_name' => $r->user->name,
                    'date' => $r->created_at->format('Y-m-d'),
                    'clock_in_time' => $r->clock_in_time,
                    'clock_out_time' => $r->clock_out_time,
                    'rest_start' => optional($r->rests->first())->break_start,
                    'rest_end' => optional($r->rests->first())->break_end,
                ];
            });

        return response()->json($records);
    }

    // 勤務詳細 API
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])   // ← user もロード
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'id' => $attendance->id,
            'user_name' => $attendance->user->name,
            'date' => $attendance->created_at->format('Y-m-d'),
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
            'rest_start' => optional($attendance->rests->first())->break_start,
            'rest_end' => optional($attendance->rests->first())->break_end,
            'note' => $attendance->note,
            'status' => $attendance->status,
        ]);
    }
}
