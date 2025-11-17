<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

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

    public function updateApi(Request $request, $id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);

        // 出退勤の更新
        $attendance->clock_in_time  = $request->clock_in_time;
        $attendance->clock_out_time = $request->clock_out_time;
        $attendance->note           = $request->note;
        $attendance->status         = 'pending';
        $attendance->save();

        // 休憩を再登録
        $attendance->rests()->delete();

        if ($request->rest_start && $request->rest_end) {

            $date = Carbon::parse($attendance->clock_in_time)->format('Y-m-d');

            $attendance->rests()->create([
                'break_start' => Carbon::parse("{$date} {$request->rest_start}"),
                'break_end'   => Carbon::parse("{$date} {$request->rest_end}"),
            ]);
        }

        return response()->json([
            'message' => 'updated',
            'attendance_id' => $attendance->id
        ]);
    }
}
