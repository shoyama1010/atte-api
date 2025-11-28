<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Rest;
use App\Models\CorrectionRequest;

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
    public function update(Request $request, $id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);

        // ▼ Before 値を退避（更新前のオリジナル）
        $beforeClockIn  = $attendance->clock_in_time;
        $beforeClockOut = $attendance->clock_out_time;

        $beforeBreakStart = optional($attendance->rests->first())->break_start;
        $beforeBreakEnd   = optional($attendance->rests->first())->break_end;

        // ▼ After（フォーム入力値）
        $afterClockIn  = $request->input('clock_in_time');
        $afterClockOut = $request->input('clock_out_time');

        $afterBreakStart = $request->rests[0]['break_start'] ?? null;
        $afterBreakEnd   = $request->rests[0]['break_end'] ?? null;

        // ▼ Attendance の更新
        $attendance->clock_in_time  = $afterClockIn;
        $attendance->clock_out_time = $afterClockOut;
        $attendance->note = $request->note;
        $attendance->save();

        // ▼ 休憩時間の再登録
        $attendance->rests()->delete();

        if ($afterBreakStart && $afterBreakEnd) {
            $date = Carbon::parse($attendance->clock_in_time)->format('Y-m-d');
            $attendance->rests()->create([
                'break_start' => Carbon::parse("$date $afterBreakStart"),
                'break_end'   => Carbon::parse("$date $afterBreakEnd"),
            ]);
        }

        // ▼ 修正申請データの登録（重要）
        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => Auth::id(),
            'admin_id' => null,
            'request_type' => 'time_change',
            'reason' => $request->note,

            // Before
            'before_clock_in'  => $beforeClockIn,
            'before_clock_out' => $beforeClockOut,
            'before_break_start' => $beforeBreakStart,
            'before_break_end'   => $beforeBreakEnd,

            // After
            'after_clock_in'  => $afterClockIn,
            'after_clock_out' => $afterClockOut,
            'after_break_start' => $afterBreakStart,
            'after_break_end'   => $afterBreakEnd,

            'status' => 'pending'
        ]);

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('success', '修正申請を送信しました。（承認待ち）');
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
