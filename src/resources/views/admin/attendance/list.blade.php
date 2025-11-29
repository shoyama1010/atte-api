@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">

    <div class="attendance-list-container">
        {{-- 日付ヘッダー --}}
        <h2 class="attendance-date-title">{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠</h2>
        {{-- 日付ナビゲーション --}}
        <div class="date-nav">
            <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}"
                class="nav-btn">← 前日</a>
            <form method="GET" action="{{ route('admin.attendance.list') }}" class="date-form">
                <input type="date" name="date" value="{{ $date }}" class="date-input">
                <button type="submit" class="btn-go">表示</button>
            </form>
            <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}"
                class="nav-btn">翌日 →</a>
        </div>
        {{-- 勤怠一覧テーブル --}}
        @if ($attendances->isEmpty())
            <p class="no-data">該当日の勤怠データはありません。</p>
        @else
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        @php
                            $clockIn = $attendance->clock_in_time
                                ? \Carbon\Carbon::parse($attendance->clock_in_time)
                                : null;
                            $clockOut = $attendance->clock_out_time
                                ? \Carbon\Carbon::parse($attendance->clock_out_time)
                                : null;
                            $breakStart = $attendance->break_start
                                ? \Carbon\Carbon::parse($attendance->break_start)
                                : null;
                            $breakEnd = $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end) : null;

                            $workMinutes = 0;
                            if ($clockIn && $clockOut) {
                                $workMinutes = $clockOut->diffInMinutes($clockIn);
                                if ($breakStart && $breakEnd) {
                                    $workMinutes -= $breakEnd->diffInMinutes($breakStart);
                                }
                            }
                            $workHours = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                        @endphp
                        <tr>
                            <td>{{ $attendance->user->name }}</td>
                            <td>{{ optional($clockIn)->format('H:i') ?? '-' }}</td>
                            <td>{{ optional($clockOut)->format('H:i') ?? '-' }}</td>
                            <td>
                                @if ($breakStart && $breakEnd)
                                    {{ $breakStart->diffInHours($breakEnd) }}:{{ str_pad($breakStart->diffInMinutes($breakEnd) % 60, 2, '0', STR_PAD_LEFT) }}
                                @else
                                    0:00
                                @endif
                            </td>
                            <td>{{ $workHours }}</td>
                            <td>
                                <a href="{{ route('admin.attendance.edit', $attendance->id) }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
