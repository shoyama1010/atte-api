<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Rest;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // 自分の勤怠データを新しい順に取得
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
        $user = Auth::user();

        // 今日の出勤データを取得（退勤前のレコード）
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return back()->with('error', '勤務中のデータが見つかりません。');
        }

        // restsテーブルに新規追加（休憩開始）
        Rest::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->format('H:i:s'),
        ]);
        // $attendance->rests()->create([
        //     'break_start' => Carbon::now()->format('H:i:s'),
        // ]);
        // 勤務状態を休憩中に変更
        $attendance->update(['status' => 'on_break']);

        return redirect()->route('attendance.index')->with('message', '休憩を開始しました。');
    }

    public function breakEnd()
    {
        $user = Auth::user();

        // 今日の出勤データを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return back()->with('error', '勤務中のデータが見つかりません。');
        }

        // 直近の休憩レコード（まだbreak_endがnullのもの）を取得
        $rest = $attendance->rests()
            ->whereNull('break_end')
            ->latest()
            ->first();

        if ($rest) {
            $rest->update([
                'break_end' => Carbon::now()->format('H:i:s'),
            ]);

            // 状態を「出勤中」に戻す
            $attendance->update(['status' => 'working']);

            return redirect()->route('attendance.index')->with('message', '休憩を終了しました。');
        }

        return redirect()->route('attendance.index')->with('error', '休憩中データが見つかりません。');
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

    public function list()
    {
        $user = Auth::user();

        // 自分の勤怠データを新しい順に取得
        $attendances = Attendance::where('user_id', $user->id)
            // ->orderBy('created_at', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->get();
        // ->paginate(10);

        // return view('attendance.list', compact('user', 'attendances'));
        return view('attendance.list', compact('attendances'));
    }

    // 勤務詳細
    public function detail($id)
    {
        $user = Auth::user();

        $attendance = Attendance::with('rests')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
        // $attendance = Attendance::where('id', $id)
        //     ->where('user_id', $user->id)
        //     ->firstOrFail();

        return view('attendance.detail', compact('user', 'attendance'));
    }


    // 休憩回数分のレコードを保存
    public function store(Request $request)
    {
        $attendance = Attendance::create([
            'user_id' => auth()->id(),
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
        ]);
        // 休憩の登録（複数対応）
        if ($request->has('rests')) {
            foreach ($request->rests as $rest) {
                if (!empty($rest['break_start']) && !empty($rest['break_end'])) {
                    $attendance->rests()->create([
                        'break_start' => $rest['break_start'],
                        'break_end' => $rest['break_end'],
                    ]);
                }
            }
        }
        return redirect()->route('attendance.index')
            ->with('success', '勤務を登録しました');
    }

    public function create()
    {
        return view('attendance.create');
    }
}
