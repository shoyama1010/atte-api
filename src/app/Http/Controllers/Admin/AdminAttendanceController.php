<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 日付を取得（指定がなければ今日）
        $date = $request->input('date', Carbon::today()->toDateString());

        // 該当日の勤怠一覧を取得

        $attendances = Attendance::with('user')
            ->whereDate('clock_in_time', $date) // ←ここを変更
            ->get();

        return view('admin.attendance.list', compact('attendances', 'date'));
    }

    public function show($id)
    {
        // 特定ユーザーの勤怠データを取得
        $attendance = Attendance::with('user')->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    public function edit($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('admin.attendance.edit', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i|after:clock_in_time',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'remarks' => 'nullable|string|max:255',
        ]);

        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
            'break_start' => $request->break_start,
            'break_end' => $request->break_end,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('admin.attendance.list')
            ->with('success', '勤怠情報を修正しました。');
    }
}
