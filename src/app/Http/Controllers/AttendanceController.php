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

    public function list()
    {
        $user = Auth::user();

        // 自分の勤怠データを新しい順に取得
        $attendances = Attendance::where('user_id', $user->id)
            // ->orderBy('created_at', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->get();

        return view('attendance.list', compact('attendances'));
    }

    public function detail($id)
    {
        $user = Auth::user();

        // 勤怠データ＋休憩・修正申請データをまとめて取得
        $attendance = Attendance::with(['rests', 'correctionRequest'])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        // 関連を再読込してキャッシュをクリア
        $attendance->load('correctionRequest');

        // ステータスを correction_requests の状態優先で確認
        $status = $attendance->correctionRequest->status ?? $attendance->status;

        return view('attendance.detail', compact('user', 'attendance', 'status'));
    }

    /**
     * 勤務情報の更新処理
     */
    public function update(Request $request, $id)
    {
        // 対象の出勤データを取得
        $attendance = Attendance::with('rests')->findOrFail($id);

        // 出退勤時間を更新
        $attendance->clock_in_time = $request->input('clock_in_time');
        $attendance->clock_out_time = $request->input('clock_out_time');
        $attendance->note = $request->input('note');
        // ✅ ここを追加：修正後は承認待ちに変更
        $attendance->status = 'pending';

        $attendance->save();

        // 既存の休憩データを一旦削除して再登録（複数対応）
        $attendance->rests()->delete();

        if ($request->has('rests')) {
            foreach ($request->rests as $rest) {
                if (!empty($rest['break_start']) && !empty($rest['break_end'])) {

                    // ✅ 日付を出勤日の created_at から取る（または clock_in_time でもOK）
                    $date = Carbon::parse($attendance->clock_in_time)->format('Y-m-d');

                    $attendance->rests()->create([
                        'break_start' => Carbon::parse("{$date} {$rest['break_start']}"),
                        'break_end'   => Carbon::parse("{$date} {$rest['break_end']}"),
                    ]);
                }
            }
        }

        // 修正申請の登録処理
        CorrectionRequest::create([
            'attendance_id'   => $attendance->id,
            'user_id'         => auth()->id(),
            'admin_id'        => null,
            'request_type'    => 'time_change',
            'before_clock_in' => $attendance->getOriginal('clock_in_time'),
            'before_clock_out' => $attendance->getOriginal('clock_out_time'),
            'after_clock_in'  => $request->input('clock_in_time'),
            'after_clock_out' => $request->input('clock_out_time'),
            'reason'          => $request->input('note'),
            'status'          => 'pending', // ← これが重要！
        ]);

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('success', '勤務情報を更新しました。');
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
