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
}
