<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
  public function list(Request $request)
  {
    $date = $request->query('date', now()->format('Y-m-d'));

    try {
      $targetDate = Carbon::parse($date)->format('Y-m-d');
    } catch (\Throwable $e) {
      return response()->json([
        'message' => '日付形式が不正です。',
      ], 422);
    }

    $startOfDay = Carbon::parse($targetDate)->startOfDay();
    $endOfDay = Carbon::parse($targetDate)->endOfDay();

    $records = Attendance::with(['user', 'rests'])
      ->whereBetween('clock_in_time', [$startOfDay, $endOfDay])
      ->orderBy('clock_in_time', 'asc')
      ->get()
      ->map(function (Attendance $attendance) {
        $restTotalSec = $attendance->rests->reduce(function (int $carry, $rest) {
          if (!$rest->break_start || !$rest->break_end) {
            return $carry;
          }

          return $carry + (
            Carbon::parse($rest->break_end)->timestamp
            - Carbon::parse($rest->break_start)->timestamp
          );
        }, 0);

        $totalWork = null;

        if ($attendance->clock_in_time && $attendance->clock_out_time) {
          $workSec =
            Carbon::parse($attendance->clock_out_time)->timestamp
            - Carbon::parse($attendance->clock_in_time)->timestamp
            - $restTotalSec;

          if ($workSec >= 0) {
            $hours = floor($workSec / 3600);
            $minutes = floor(($workSec % 3600) / 60);
            $totalWork = sprintf('%02d:%02d', $hours, $minutes);
          }
        }

        $restHours = floor($restTotalSec / 3600);
        $restMinutes = floor(($restTotalSec % 3600) / 60);
        $restTotal = sprintf('%02d:%02d', $restHours, $restMinutes);

        return [
          'id' => $attendance->id,
          'user_id' => $attendance->user_id,
          'user_name' => optional($attendance->user)->name,
          'date' => optional($attendance->clock_in_time)?->format('Y-m-d'),
          'clock_in_time' => optional($attendance->clock_in_time)?->format('H:i'),
          'clock_out_time' => optional($attendance->clock_out_time)?->format('H:i'),
          'rest_total' => $restTotal,
          'total_work' => $totalWork,
        ];
      })
      ->values();

    return response()->json([
      'date' => $targetDate,
      'records' => $records,
    ]);
  }

  public function show($id)
  {
    $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

    return response()->json([
      'id' => $attendance->id,
      'user_id' => $attendance->user_id,
      'user_name' => optional($attendance->user)->name,
      'date' => optional($attendance->clock_in_time)?->format('Y-m-d'),
      'clock_in_time' => optional($attendance->clock_in_time)?->format('H:i'),
      'clock_out_time' => optional($attendance->clock_out_time)?->format('H:i'),
      'rests' => $attendance->rests->map(function ($rest) {
        return [
          'break_start' => optional($rest->break_start)?->format('H:i'),
          'break_end' => optional($rest->break_end)?->format('H:i'),
        ];
      })->values(),
      'note' => $attendance->note,
      'status' => $attendance->status,
    ]);
  }

  public function staffMonthly(Request $request, $id)
  {
    $user = User::findOrFail($id);
    $month = $request->query('month', now()->format('Y-m'));

    try {
      $targetMonth = Carbon::createFromFormat('Y-m', $month);
    } catch (\Throwable $e) {
      return response()->json([
        'message' => '月形式が不正です。',
      ], 422);
    }

    $start = $targetMonth->copy()->startOfMonth();
    $end = $targetMonth->copy()->endOfMonth();

    $records = Attendance::with('rests')
      ->where('user_id', $id)
      ->whereBetween('clock_in_time', [$start, $end])
      ->orderBy('clock_in_time', 'desc')
      ->get()
      ->map(function (Attendance $attendance) {
        $restTotalSec = $attendance->rests->reduce(function (int $carry, $rest) {
          if (!$rest->break_start || !$rest->break_end) {
            return $carry;
          }

          return $carry + (
            Carbon::parse($rest->break_end)->timestamp
            - Carbon::parse($rest->break_start)->timestamp
          );
        }, 0);

        $restHours = floor($restTotalSec / 3600);
        $restMinutes = floor(($restTotalSec % 3600) / 60);
        $restTotal = sprintf('%02d:%02d', $restHours, $restMinutes);

        $totalWork = null;
        if ($attendance->clock_in_time && $attendance->clock_out_time) {
          $workSec =
            Carbon::parse($attendance->clock_out_time)->timestamp
            - Carbon::parse($attendance->clock_in_time)->timestamp
            - $restTotalSec;

          if ($workSec >= 0) {
            $hours = floor($workSec / 3600);
            $minutes = floor(($workSec % 3600) / 60);
            $totalWork = sprintf('%02d:%02d', $hours, $minutes);
          }
        }

        return [
          'id' => $attendance->id,
          'date' => optional($attendance->clock_in_time)?->format('Y/m/d'),
          'clock_in_time' => optional($attendance->clock_in_time)?->format('H:i'),
          'clock_out_time' => optional($attendance->clock_out_time)?->format('H:i'),
          'rest_total' => $restTotal,
          'total_work' => $totalWork,
        ];
      })
      ->values();

    return response()->json([
      'user' => [
        'id' => $user->id,
        'name' => $user->name,
      ],
      'month' => $targetMonth->format('Y-m'),
      'records' => $records,
    ]);
  }

  public function staffMonthlyCsv(Request $request, $id): StreamedResponse
  {
    $user = User::findOrFail($id);
    $month = $request->query('month', now()->format('Y-m'));

    $targetMonth = Carbon::createFromFormat('Y-m', $month);
    $start = $targetMonth->copy()->startOfMonth();
    $end = $targetMonth->copy()->endOfMonth();

    $records = Attendance::with('rests')
      ->where('user_id', $id)
      ->whereBetween('clock_in_time', [$start, $end])
      ->orderBy('clock_in_time', 'desc')
      ->get();

    $filename = sprintf('attendance_%s_%s.csv', $user->id, $targetMonth->format('Y_m'));

    return response()->streamDownload(function () use ($records) {
      $handle = fopen('php://output', 'w');

      fwrite($handle, "\xEF\xBB\xBF"); // 追加

      fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

      foreach ($records as $attendance) {
        $restTotalSec = $attendance->rests->reduce(function (int $carry, $rest) {
          if (!$rest->break_start || !$rest->break_end) {
            return $carry;
          }

          return $carry + (
            Carbon::parse($rest->break_end)->timestamp
            - Carbon::parse($rest->break_start)->timestamp
          );
        }, 0);

        $restHours = floor($restTotalSec / 3600);
        $restMinutes = floor(($restTotalSec % 3600) / 60);
        $restTotal = sprintf('%02d:%02d', $restHours, $restMinutes);

        $totalWork = '';
        if ($attendance->clock_in_time && $attendance->clock_out_time) {
          $workSec =
            Carbon::parse($attendance->clock_out_time)->timestamp
            - Carbon::parse($attendance->clock_in_time)->timestamp
            - $restTotalSec;

          if ($workSec >= 0) {
            $hours = floor($workSec / 3600);
            $minutes = floor(($workSec % 3600) / 60);
            $totalWork = sprintf('%02d:%02d', $hours, $minutes);
          }
        }

        fputcsv($handle, [
          optional($attendance->clock_in_time)?->format('Y/m/d'),
          optional($attendance->clock_in_time)?->format('H:i'),
          optional($attendance->clock_out_time)?->format('H:i'),
          $restTotal,
          $totalWork,
        ]);
      }

      fclose($handle);

    }, $filename, [
      'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
  }
}
