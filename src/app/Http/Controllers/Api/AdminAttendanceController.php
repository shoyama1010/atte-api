<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
