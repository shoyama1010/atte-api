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

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 日付を取得（指定がなければ今日）
        $date = $request->input('date', Carbon::today()->toDateString());

        // 該当日の勤怠一覧を取得
        $attendances = Attendance::with('user')
            ->whereDate('clock_in_time', $date) // ←ここを変更
            // ->whereDate('date', $date)
            ->get();

        return view('admin.attendance.list', compact('attendances', 'date'));
    }

    // 既存: 勤怠修正画面（スタッフ詳細扱い）
    public function show($id)
    {
        // 特定ユーザーの勤怠データを取得
        $attendance = Attendance::with('user')->findOrFail($id);
        // 勤務者（ユーザー情報）
        $staff = $attendance->user;
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

        $breakHours = sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
        // return view('admin.attendance.edit', compact('attendance'));
        return view('admin.attendance.detail', compact(
            'attendance',
            'staff',
            'clockIn',
            'clockOut',
            'breakHours'
        ));
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

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        // 修正前の時刻を記録
        $before = [
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
            'break_start' => $attendance->break_start,
            'break_end' => $attendance->break_end,
        ];
        // 勤怠テーブル更新
        $attendance->update([
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
            'break_start' => $request->break_start,
            'break_end' => $request->break_end,
            'remarks' => $request->remarks,
        ]);
        // 修正後の時刻を記録
        $after = [
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
            'break_start' => $attendance->break_start,
            'break_end' => $attendance->break_end,
        ];
        // 修正履歴をcorrection_requestsテーブルに保存
        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'admin_id' => auth()->id(),
            'request_type' => 'time_change',
            'before_time' => json_encode($before),  // 変更前
            'after_time' => json_encode($after),    // 変更後
            'reason' => $request->remarks,
            'status' => 'approved',
        ]);
        return redirect()->route('admin.attendance.list')
            ->with('success', '勤怠情報を修正しました。');
    }
    // スタッフ別勤怠一覧
    public function staffList($id)
    {
        $staff = User::findOrFail($id);
        $date = now()->toDateString(); // ← デフォルトで今日

        // 🔸休憩データもまとめて取得
        $attendances = Attendance::with('rests')
            ->where('user_id', $id)
            ->orderBy('clock_in_time', 'desc')
            ->paginate(20);

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
            $attendance->break_total = $totalMinutes > 0
                ? sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60)
                : '-';
        }

        return view('admin.attendance.staff_list', compact('staff', 'attendances'));
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
