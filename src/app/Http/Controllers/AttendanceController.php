<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;


class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        return view('attendance.index', compact('user', 'attendance'));
    }

    public function clockIn()
    {
        $user = Auth::user();

        Attendance::create([
            'user_id' => $user->id,
            'clock_in_time' => Carbon::now(),
            'status' => 'working',
        ]);

        return redirect()->route('attendance.index')->with('message', '出勤しました');
    }

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->first();

        $attendance->update([
            'break_start' => Carbon::now(),
            'status' => 'on_break'
        ]);

        return redirect()->route('attendance.index')->with('message', '休憩を開始しました');
    }

    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->first();

        $attendance->update([
            'break_end' => Carbon::now(),
            'status' => 'working'
        ]);

        return redirect()->route('attendance.index')->with('message', '休憩を終了しました');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->first();

        $attendance->update([
            'clock_out_time' => Carbon::now(),
            'status' => 'left'
        ]);

        return redirect()->route('attendance.index')->with('message', '退勤しました');
    }
}
