<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CorrectionRequest;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        // $user = Auth::user();
        $status = $request->query('status', 'pending');

        $requests = CorrectionRequest::with(['attendance', 'attendance.user'])
            // ->whereHas('attendance', fn($q) => $q->where('user_id', $user->id))
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'status' => $r->status,
                'user_name' => $r->attendance->user->name,
                'request_date' => $r->created_at->format('Y-m-d'),
                'target_date' => $r->attendance->created_at->format('Y-m-d'),
                'reason' => $r->reason,
            ]);

        return response()->json($requests);
    }

    public function show($id)
    {
        $request = CorrectionRequest::with(['attendance', 'attendance.user'])->findOrFail($id);

        return response()->json([
            'id' => $request->id,
            'user_name' => $request->attendance->user->name,
            'status' => $request->status,
            'reason' => $request->reason,
            'request_date' => $request->created_at->format('Y-m-d'),
            'target_date' => $request->attendance->created_at->format('Y-m-d'),
            'clock_in_time' => $request->attendance->clock_in_time,
            'clock_out_time' => $request->attendance->clock_out_time,
            'note' => $request->attendance->note,
        ]);
    }
}
