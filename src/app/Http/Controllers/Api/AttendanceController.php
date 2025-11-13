<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function show($id)
    {
        $user = Auth::user();

        $attendance = Attendance::with('rests')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'id' => $attendance->id,
            'user_name' => $user->name,
            'date' => $attendance->created_at->format('Y-m-d'),
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
            'rest_start' => $attendance->rests->first()->break_start ?? null,
            'rest_end' => $attendance->rests->first()->break_end ?? null,
            'note' => $attendance->note,
            'status' => $attendance->status,
        ]);
    }
}
