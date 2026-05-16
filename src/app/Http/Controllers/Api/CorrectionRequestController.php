<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CorrectionRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = CorrectionRequest::with(['attendance', 'attendance.user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'status' => $r->status,
                    'user_name' => optional($r->attendance->user)->name,
                    'request_date' => optional($r->created_at)?->format('Y-m-d'),
                    'target_date' => optional($r->attendance->created_at)?->format('Y-m-d'),
                    'reason' => $r->reason,
                ];
            });

        return response()->json($requests);
    }
    
    public function show($id)
    {
        $request = CorrectionRequest::with(['attendance.user'])->findOrFail($id);

        return response()->json([
            'id' => $request->id,
            'user_id' => $request->attendance->user->id,
            'user_name' => $request->attendance->user->name,

            'status' => $request->status,
            'request_date' => $request->created_at->format('Y-m-d'),
            'target_date' => $request->attendance->created_at->format('Y-m-d'),

            'reason' => $request->reason,

            'before_clock_in' => optional($request->before_clock_in)?->format('H:i'),
            'before_clock_out' => optional($request->before_clock_out)?->format('H:i'),
            'before_break_start' => optional($request->before_break_start)?->format('H:i'),
            'before_break_end' => optional($request->before_break_end)?->format('H:i'),

            'after_clock_in' => optional($request->after_clock_in)?->format('H:i'),
            'after_clock_out' => optional($request->after_clock_out)?->format('H:i'),
            'after_break_start' => optional($request->after_break_start)?->format('H:i'),
            'after_break_end' => optional($request->after_break_end)?->format('H:i'),
        ]);
    }

    public function approve($id)
    {
        $correction = CorrectionRequest::findOrFail($id);
        $attendance = Attendance::findOrFail($correction->attendance_id);

        $attendance->update([
            'clock_in_time'  => $correction->after_clock_in,
            'clock_out_time' => $correction->after_clock_out,
            'break_start'    => $correction->after_break_start,
            'break_end'      => $correction->after_break_end,
        ]);

        $correction->update([
            'status'   => 'approved',
            'admin_id' => Auth::guard('admin')->id(),
        ]);

        return response()->json([
            'message' => 'approved'
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all()); // ← ★ここに入れる（最初の行）

        $attendance = Attendance::findOrFail($request->attendance_id);

        // 日付を取得（ここ超重要）
        $date = Carbon::parse($attendance->clock_in_time)->format('Y-m-d');

        // datetimeに変換
        $afterClockIn = $request->after_clock_in
            ? Carbon::parse($date . ' ' . $request->after_clock_in)->format('Y-m-d H:i:s')
            : null;

        $afterClockOut = $request->after_clock_out
            ? Carbon::parse($date . ' ' . $request->after_clock_out)->format('Y-m-d H:i:s')
            : null;
        
        // 休憩（配列対応）
        $rests = $request->rests ?? [];

        $afterBreakStart = null;
        $afterBreakEnd = null;

        if (!empty($rests)) {
            // 1件目を保存（DB構造に合わせる）
            $firstRest = $rests[0] ?? null;
            $afterBreakStart = isset($firstRest['break_start'])
                ? Carbon::parse($date . ' ' . $firstRest['break_start'])->format('Y-m-d H:i:s')
                : null;

            $afterBreakEnd = isset($firstRest['break_end'])
                ? Carbon::parse($date . ' ' . $firstRest['break_end'])->format('Y-m-d H:i:s')
                : null;
        }

        $correction = CorrectionRequest::create([
            'attendance_id'   => $attendance->id,
            // 'user_id'         => auth()->id(),
            'user_id' => $attendance->user_id, // ← ★ここに変更
            'after_clock_in'  => $afterClockIn,
            'after_clock_out' => $afterClockOut,
            'after_break_start' => $afterBreakStart,
            'after_break_end'   => $afterBreakEnd,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => '申請完了',
            'data' => $correction
        ]);
    }
}
