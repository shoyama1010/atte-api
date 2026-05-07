<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CorrectionRequest;
use App\Models\Attendance;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        try {
            $requests = CorrectionRequest::with(['attendance.user'])
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'status' => $r->status,

                        'user_name' => optional(optional($r->attendance)->user)->name ?? '不明',

                        'request_date' => optional($r->created_at)?->format('Y-m-d'),

                        'target_date' => $r->attendance->clock_in_time
                            ? \Carbon\Carbon::parse($r->attendance->clock_in_time)->format('Y-m-d')
                            : '不明',
                        // 'target_date' => optional($r->attendance)?->date
                        //     ? \Carbon\Carbon::parse($r->attendance->date)->format('Y-m-d')
                        //     : '不明',

                        'reason' => $r->reason,
                    ];
                });

            return response()->json($requests);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $request = CorrectionRequest::with(['attendance.user'])->findOrFail($id);

        return response()->json([
            'id' => $request->id,
            'user_name' => $request->attendance->user->name,

            // ステータス
            'status' => $request->status,
            'request_date' => $request->created_at->format('Y-m-d'),

            // 対象勤務日
            'target_date' => $request->attendance->created_at->format('Y-m-d'),

            // 修正理由
            'reason' => $request->reason,

            // before (元の値)
            'before_clock_in'     => $request->before_clock_in,
            'before_clock_out'    => $request->before_clock_out,
            'before_break_start'  => $request->before_break_start,
            'before_break_end'    => $request->before_break_end,

            // after (修正後)
            'after_clock_in'      => $request->after_clock_in,
            'after_clock_out'     => $request->after_clock_out,
            'after_break_start'   => $request->after_break_start,
            'after_break_end'     => $request->after_break_end,
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
}
