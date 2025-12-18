<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Rest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class AttendanceController extends Controller
{
    // 勤務一覧 API
    public function index()
    {
        $records = Attendance::with(['user', 'rests'])   // ← rests を追加
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'user_id' => $r->user_id,
                    'user_name' => $r->user->name,
                    'date' => $r->created_at->format('Y-m-d'),
                    'clock_in_time' => $r->clock_in_time,
                    'clock_out_time' => $r->clock_out_time,
                    'rest_start' => optional($r->rests->first())->break_start,
                    'rest_end' => optional($r->rests->first())->break_end,
                ];
            });

        return response()->json($records);
    }

    // 勤務詳細 API-
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])   // ← user もロード
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'id' => $attendance->id,
            'user_name' => $attendance->user->name,
            'date' => $attendance->created_at->format('Y-m-d'),
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
            'rest_start' => optional($attendance->rests->first())->break_start,
            'rest_end' => optional($attendance->rests->first())->break_end,
            'note' => $attendance->note,
            'status' => $attendance->status,
        ]);
    }

    public function updateApi(Request $request, $id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);
        // 出退勤の更新
        $attendance->update([
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
            'note' => $request->note,
            'status' => 'pending',
        ]);

        // 休憩を再登録
        $attendance->rests()->delete();

        if ($request->rest_start && $request->rest_end) {
            $date = Carbon::parse($attendance->clock_in_time)->format('Y-m-d');

            $attendance->rests()->create([
                'break_start' => Carbon::parse("{$date} {$request->rest_start}"),
                'break_end'   => Carbon::parse("{$date} {$request->rest_end}"),
            ]);
        }

        return response()->json([
            'message' => 'updated',
            'attendance_id' => $attendance->id
        ]);
    }

    public function listByUser($userId)
    {
        $month = request()->query('month', now()->format('Y-m'));
        $start = $month . '-01';
        $end   = date('Y-m-t', strtotime($start));

        $records = Attendance::where('user_id', $userId)
            // ->whereBetween('date', [$start, $end])
            ->whereBetween('clock_in_time', [$start, $end])
            // ->orderBy('date', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    // 'date' => $a->date,
                    'date' => substr($a->clock_in_time, 0, 10),
                    'clock_in_time' => $a->clock_in_time,
                    'clock_out_time' => $a->clock_out_time,
                    'rest_total' => $a->rest_total,
                    'total_work' => $a->total_work,
                ];
            });

        return response()->json([
            'user_name' => User::find($userId)->name,
            'records' => $records,
        ]);
    }

    public function userList(Request $request, $id)
    {
        // -------- (1) ユーザー情報取得 -------- //
        $user = User::findOrFail($id);
        // -------- (2) 月の指定（?month=2025-11） -------- //
        $month = $request->query('month', now()->format('Y-m'));
        // $month = $request->query('month');
        // if (!$month) {
        //     $month = now()->format('Y-m'); // 指定なし → 今月
        // }
        // 月の開始・終了日
        $start = $month . '-01';
        $end   = date('Y-m-t', strtotime($start));

        // -------- (3) 月の勤怠データ取得 -------- //
        $attendances = Attendance::where('user_id', $id)
            // ->whereDate('date', '>=', $start)
            ->whereBetween('clock_in_time', [$start, $end])
            // ->whereDate('date', '<=', $end)
            // ->orderBy('date', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->get();

        // -------- (4) 整形して返す配列 -------- //
        $result = $attendances->map(function ($a) {

            // ★ 休憩合計
            $restTotal = Rest::where('attendance_id', $a->id)
                ->get()
                ->map(function ($r) {
                    // 休憩時間差分：start → end
                    if (!$r->rest_start || !$r->rest_end) return 0;
                    return strtotime($r->rest_end) - strtotime($r->rest_start);
                })
                ->sum();

            // ★ 労働時間計算（退勤 - 出勤 - 休憩）
            $totalWork = null;
            if ($a->clock_in_time && $a->clock_out_time) {
                $workSec = strtotime($a->clock_out_time) - strtotime($a->clock_in_time) - $restTotal;

                if ($workSec > 0) {
                    $h = floor($workSec / 3600);
                    $m = floor(($workSec % 3600) / 60);
                    $totalWork = sprintf('%02d:%02d', $h, $m);
                }
            }

            return [
                'id' => $a->id,
                // 'date' => $a->date,
                'date' => substr($a->clock_in_time, 0, 10),
                'clock_in_time' => $a->clock_in_time ,
                'clock_out_time' => $a->clock_out_time ,
                'rest_total' => gmdate('H:i', $restTotal),
                'total_work' => $totalWork ,
            ];
        });

        // -------- (5) Next.js 用レスポンス -------- //
        return response()->json([
            'user_name' => $user->name,
            // 'attendances' => $result,
            'records' => $result,
        ]);
    }

    public function userMonthly($id, Request $request)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $month = $request->query('month') ?? now()->format('Y-m');
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        // ✅ rests リレーション付きで取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('clock_in_time', [$start, $end])
            ->with('rests')
            ->orderBy('clock_in_time', 'desc')
            ->get();

        // JSON用に整形
        $records = $attendances->map(function ($a) {
            // ✅ 休憩時間を文字列に整形
            $restDisplay = $a->rests->map(function ($r) {
                if ($r->break_start && $r->break_end) {
                    // return substr($r->break_start, 0, 5) . ' ～ ' . substr($r->break_end, 0, 5);
                    return Carbon::parse($r->break_start)->format('H:i') . ' ～ ' .
                        Carbon::parse($r->break_end)->format('H:i');
                }
                return null;
            })->filter()->implode(' ／ ');

            if ($restDisplay === '') {
                $restDisplay = '―';
            }

            // ✅ 合計休憩時間を算出
            $restTotalSec = $a->rests->reduce(function ($carry, $r) {
                if (!$r->break_start || !$r->break_end) return $carry;
                $start = Carbon::parse($r->break_start);
                $end   = Carbon::parse($r->break_end);
                return $carry + $end->diffInSeconds($start);
            }, 0);

        // ✅ 労働時間
        $totalWork = null;
        if ($a->clock_in_time && $a->clock_out_time) {
            try {
                $clockIn  = Carbon::parse($a->clock_in_time);
                $clockOut = Carbon::parse($a->clock_out_time);
                    // ✅ 差分計算
                    // $workSec = $clockOut->diffInSeconds($clockIn) - $restTotalSec;
                    $workSec = abs($clockOut->diffInSeconds($clockIn) - $restTotalSec);

                    // Log::info('WorkSec:', ['value' => $workSec]);

                if ($workSec > 0) {
                    $h = floor($workSec / 3600);
                    $m = floor(($workSec % 3600) / 60);
                    $totalWork = sprintf('%02d:%02d', $h, $m);
                }
            } catch (Exception $e) {
                $totalWork = null; // parseエラー対策
            }
        }

            return [
                'id' => $a->id,
                'date' => Carbon::parse($a->clock_in_time)->format('Y-m-d'),
                'clock_in_time' => Carbon::parse($a->clock_in_time)->format('H:i'),
                'clock_out_time' => Carbon::parse($a->clock_out_time)->format('H:i'),
                // 'date' => substr($a->clock_in_time, 0, 10),
                // 'clock_in_time' => substr($a->clock_in_time, 11, 5),
                // 'clock_out_time' => substr($a->clock_out_time, 11, 5),
                'rest_display' => $restDisplay, // ← ここで返す
                'total_work' => $totalWork,
            ];
        });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'records' => $records,
        ]);
    }

    // ログイン中ユーザーの勤怠一覧取得 API
    public function userAttendances(Request $request)
    {
        $user = $request->user();
        // $user = Auth::user();
        $records = Attendance::with('rests')
            ->where('user_id', $user->id)
            // ->orderBy('date', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->get()

            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'date' => substr($r->clock_in_time, 0, 10),
                    'clock_in_time' => $r->clock_in_time,
                    'clock_out_time' => $r->clock_out_time,
                    'rest_start' => optional($r->rests->first())->break_start,
                    'rest_end' => optional($r->rests->first())->break_end,
                ];
            });
        return response()->json($records);
    }
}
