@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
    <div class="attendance-list-container">
        <h2>勤務一覧：{{ Auth::user()->name }} </h2>

        @if ($attendances->isEmpty())
            <p>現在、勤務記録はありません。</p>
        @else
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩時間</th>
                        <th>合計</th>
                        {{-- <th>ステータス</th> --}}
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                       @php
                       // 出勤・退勤・休憩時間の計算
                        $clockIn = $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time) : null;
                        $clockOut = $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time) : null;
                        $breakStart = $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start) : null;
                        $breakEnd = $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end) : null;
                        // 休憩時間（分単位）
                        $breakDuration = ($breakStart && $breakEnd) ? $breakEnd->diffInMinutes($breakStart) : 0;
                        // 総勤務時間（出勤～退勤） - 休憩
                        $totalDuration = ($clockIn && $clockOut)
                            ? $clockOut->diffInMinutes($clockIn) - $breakDuration
                            : 0;
                        // 表示形式（h:i）
                        $breakTimeFormatted = sprintf('%02d:%02d', floor($breakDuration / 60), $breakDuration % 60);
                        $totalTimeFormatted = sprintf('%02d:%02d', floor($totalDuration / 60), $totalDuration % 60);
                       @endphp

                        <tr>
                            <td>{{ optional($clockIn)->format('Y/m/d') ?? '-' }}</td>
                            <td>{{ optional($clockIn)->format('H:i') ?? '-' }}</td>
                            <td>{{ optional($clockOut)->format('H:i') ?? '-' }}</td>
                            <td>{{ $breakTimeFormatted }}</td>
                            <td>{{ $totalTimeFormatted }}</td>
                            <td>
                                <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
