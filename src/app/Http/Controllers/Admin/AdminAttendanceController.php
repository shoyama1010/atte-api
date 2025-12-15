<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\Admin\AttendanceRequest; // ← 追加
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 日付を取得（指定がなければ今日）
        $date = $request->input('date', Carbon::today()->toDateString());
        // 該当日の勤怠一覧を取得
        $attendances = Attendance::with(['user', 'rests'])
            // ->whereDate('clock_in_time', $date)
            ->whereDate('created_at', $date)
            ->orderBy('clock_in_time')
            ->get();

        // 🔹 各勤務日の休憩合計を計算
        foreach ($attendances as $attendance) {
            $totalMinutes = 0;

            foreach ($attendance->rests as $rest) {
                if ($rest->break_start && $rest->break_end) {
                    $start = Carbon::parse($rest->break_start);
                    $end   = Carbon::parse($rest->break_end);
                    $totalMinutes += $end->diffInMinutes($start);
                }
            }
            // HH:mm形式に整形
            $attendance->break_total = $totalMinutes > 0
                ? sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60)
                : '00:00';
        }

        return view('admin.attendance.list', compact('attendances', 'date'));
    }
    // 既存: 勤怠修正画面（スタッフ詳細扱い）
    public function show($id)
    {
        // 特定ユーザーの勤怠データを取得
        $attendance = Attendance::with('user', 'rests')->findOrFail($id);
        $staff = $attendance->user; // 勤務者（ユーザー情報）
        // 出退勤データ
        $clockIn  = $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time)->format('H:i') : '--:--';
        $clockOut = $attendance->clock_out_time ? Carbon::parse($attendance->clock_out_time)->format('H:i') : '--:--';
        // 休憩時間合計（restsから算出）
        $breakMinutes = 0;
        foreach ($attendance->rests as $rest) {
            if ($rest->break_start && $rest->break_end) {
                $start = Carbon::parse($rest->break_start);
                $end   = Carbon::parse($rest->break_end);
                $breakMinutes += $end->diffInMinutes($start);
            }
        }
        
        $breakHours = $breakMinutes > 0
            ? sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
            : '00:00';
        // ★ 最新の修正申請を取得（あれば）
        $correctionRequest = CorrectionRequest::where('attendance_id', $attendance->id)
            ->orderBy('id', 'desc')->first();

        return view('admin.attendance.detail', compact(
            'attendance','staff','clockIn','clockOut','breakHours','correctionRequest'
        ));
    }
    // スタッフ別勤怠一覧
    public function staffList($id)
    {
        // 対象スタッフ情報を取得（存在しない場合は404）
        $staff = User::findOrFail($id);
        $date = now()->toDateString(); // ← デフォルトで今日

        // 🔸休憩データもまとめて取得
        $attendances = Attendance::with('rests')->where('user_id', $id)
            ->orderBy('clock_in_time', 'desc')->paginate(20);

        // 🔸各出勤日の休憩合計を計算して Blade に渡す
        foreach ($attendances as $attendance) {
            $totalMinutes = 0;
            foreach ($attendance->rests as $rest) {
                if ($rest->break_start && $rest->break_end) {
                    $start = \Carbon\Carbon::parse($rest->break_start);
                    $end   = \Carbon\Carbon::parse($rest->break_end);
                    $totalMinutes += $end->diffInMinutes($start);
                }
            }
            // HH:mm形式で算出
            $attendance->break_total = $totalMinutes > 0
                ? sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60)
                : '00:00';
        }
        // ✅ Blade にスタッフ情報＋勤怠一覧を渡す
        return view('admin.attendance.staff_list', compact('staff', 'attendances'));
    }


    public function edit($id)
    {
        $attendance = Attendance::with('rests', 'user')->findOrFail($id);
        // 休憩時間合計をBladeで扱えるように計算（オプション）
        $totalRestMinutes = 0;
        foreach ($attendance->rests as $rest) {
            if ($rest->break_start && $rest->break_end) {
                $start = \Carbon\Carbon::parse($rest->break_start);
                $end = \Carbon\Carbon::parse($rest->break_end);
                $totalRestMinutes += $end->diffInMinutes($start);
            }
        }

        $attendance->total_rest_time = sprintf('%02d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);
        return view('admin.attendance.edit', compact('attendance'));
    }


    // public function update(Request $request, $id)
    public function update(AttendanceRequest $request, $id)
    {
        // 対象勤怠データ取得（休憩も含む）
        $attendance = Attendance::with('rests')->findOrFail($id);
        // ===== BEFORE（修正前情報の記録） =====
        $beforeClockIn  = $attendance->clock_in_time;
        $beforeClockOut = $attendance->clock_out_time;
        // 複数休憩対応
        $beforeRests = $attendance->rests->map(function ($rest) {
            return [
                'break_start' => $rest->break_start,
                'break_end'   => $rest->break_end,
            ];
        })->toArray();
        // ===== AFTER（修正後の情報） =====
        $attendance->clock_in_time  = $request->clock_in_time;
        $attendance->clock_out_time = $request->clock_out_time;
        $attendance->note = $request->note;
        $attendance->save();
        // ===== 休憩テーブルの再登録 =====
        $attendance->rests()->delete();  // 既存削除

        if (!empty($request->rests)) {
            foreach ($request->rests as $rest) {
                if (!empty($rest['break_start']) && !empty($rest['break_end'])) {

                    // 日付補完（出勤日付と同じ日）
                    $date = Carbon::parse($attendance->clock_in_time)->format('Y-m-d');

                    $attendance->rests()->create([
                        'break_start' => Carbon::parse("$date {$rest['break_start']}"),
                        'break_end'   => Carbon::parse("$date {$rest['break_end']}"),
                    ]);
                }
            }
        }
        // ===== AFTER（休憩を含めた修正後情報の収集） =====
        $afterRests = [];
        foreach ($attendance->rests as $rest) {
            $afterRests[] = [
                'break_start' => $rest->break_start,
                'break_end'   => $rest->break_end,
            ];
        }
        // ===== 修正履歴（CorrectionRequest）保存 =====
        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $attendance->user_id,
            'admin_id'      => auth()->id(),
            'request_type'  => 'direct_edit',  // 管理者による修正
            'reason'        => $request->note,
            'status'        => 'approved',
            // JSON 形式で保存
            'before_time' => json_encode([
                'clock_in'  => $beforeClockIn,
                'clock_out' => $beforeClockOut,
                'rests'     => $beforeRests,
            ]),
            'after_time' => json_encode([
                'clock_in'  => $attendance->clock_in_time,
                'clock_out' => $attendance->clock_out_time,
                'rests'     => $afterRests,
            ]),
        ]);
        return redirect()
            ->route('admin.attendance.detail', $attendance->id)
            ->with('success', '勤務情報を修正しました。');
    }

    // CSVエクスポート
    public function exportStaff($id)
    {
        $staff = User::findOrFail($id);

        $response = new StreamedResponse(function () use ($staff) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩開始', '休憩終了', '備考']);

            $attendances = Attendance::where('user_id', $staff->id)
                ->orderBy('date', 'desc')
                // ->orderBy('clock_in_time', 'desc')
                ->get();

            foreach ($attendances as $a) {
                fputcsv($handle, [
                    $a->date,
                    $a->clock_in_time,
                    $a->clock_out_time,
                    $a->break_start,
                    $a->break_end,
                    $a->note ?? '',
                ]);
            }

            fclose($handle);
        });

        $fileName = 'attendance_' . $staff->name . '.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
