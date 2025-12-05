<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Rest;
use App\Models\CorrectionRequest;
use App\Http\Requests\AttendanceRequest;

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

    public function list(Request $request)
    {
        $user = Auth::user();
        // 現在の表示対象月（例：2025-11）をクエリパラメータから取得。なければ今月。
        $month = $request->query('month', now()->format('Y-m'));

        // 月初・月末をCarbonで算出
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // 自分の勤怠データを新しい順に取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$startOfMonth, $endOfMonth])
            ->orderBy('clock_in_time', 'desc')
            ->get();

        // 前月・翌月のリンク用パラメータ
        $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact('attendances', 'month', 'prevMonth', 'nextMonth', 'user'));
    }

    public function detail($id)
    {
        $user = Auth::user();
        // 勤怠データ＋休憩・修正申請データをまとめて取得
        $attendance = Attendance::with(['rests', 'correctionRequest'])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        // null対策＋最新申請の取得
        $correctionRequest = $attendance->correctionRequest()->latest()->first();
        // ステータス（pending / approved / null）
        $correctionStatus = $correctionRequest->status ?? null;

        return view('attendance.detail', compact(
            'user',
            'attendance',
            'correctionRequest',
            'correctionStatus'
        ));
    }

    // ▼ 修正申請の送信（勤怠詳細画面から直接申請）
    // public function update(Request $request, $id)
    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);
        // 🔹 BEFORE（元データ保持）
        $beforeClockIn  = $attendance->clock_in_time;
        $beforeClockOut = $attendance->clock_out_time;
        $beforeRests = $attendance->rests->map(function ($r) {
            return [
                'break_start' => $r->break_start,
                'break_end'   => $r->break_end,
            ];
        })->toArray();
        // 🔹 AFTER（フォーム入力）
        $attendance->clock_in_time  = $request->clock_in_time;
        $attendance->clock_out_time = $request->clock_out_time;
        $attendance->note           = $request->note;
        $attendance->save();
        // 🔹 休憩の更新（全削除→再登録）
        $attendance->rests()->delete();

        // if ($request->has('rests')) {
        if (!empty($request->rests)) {
            $date = Carbon::parse($attendance->clock_in_time)->format('Y-m-d');

            foreach ($request->rests as $rest) {
                if (!empty($rest['break_start']) && !empty($rest['break_end'])) {
                    $attendance->rests()->create([
                        'break_start' => "{$date} {$rest['break_start']}",
                        'break_end'   => "{$date} {$rest['break_end']}",
                    ]);
                }
            }
        }
        // 🔹 CorrectionRequest（修正申請）
        CorrectionRequest::create([
            'attendance_id'     => $attendance->id,
            'user_id'           => auth()->id(),
            'admin_id'          => null,
            'request_type'      => 'time_change',
            'reason'            => $attendance->note,
            // BEFORE
            'before_clock_in'    => $beforeClockIn,
            'before_clock_out'   => $beforeClockOut,
            'before_break_start' => $beforeRests[0]['break_start'] ?? null,
            'before_break_end'   => $beforeRests[0]['break_end'] ?? null,
            // AFTER（1件目）
            'after_break_start'  => $request->rests[0]['break_start'] ?? null,
            'after_break_end'    => $request->rests[0]['break_end'] ?? null,
            'after_clock_in'     => $attendance->clock_in_time,
            'after_clock_out'    => $attendance->clock_out_time,
            'status' => 'pending',
        ]);
        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('success', '修正申請を送信しました。');
    }


    // 休憩回数分のレコードを保存
    public function store(Request $request)
    {
        $attendance = Attendance::create([
            'user_id' => auth()->id(),
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
        ]);
        if ($request->has('rests')) { // 休憩の登録（複数対応）
            foreach ($request->rests as $rest) {
                if (!empty($rest['break_start']) && !empty($rest['break_end'])) {
                    $attendance->rests()->create([
                        'break_start' => Carbon::parse($attendance->created_at->format('Y-m-d') . ' ' . $rest['break_start']),
                        'break_end'   => Carbon::parse($attendance->created_at->format('Y-m-d') . ' ' . $rest['break_end']),
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
