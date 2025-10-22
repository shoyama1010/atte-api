@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">

@section('content')
    <div class="attendance-container">

        <h2>勤怠一覧</h2>
        <div class="attendance-date">
            <a
                href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}">←
                前日</a>
            <input type="date" value="{{ $date }}" onchange="location.href='?date=' + this.value;">
            <a
                href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}">翌日
                →</a>
        </div>

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
                @forelse ($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ optional($attendance->clock_in_time)->format('H:i') ?? '-' }}</td>
                        <td>{{ optional($attendance->clock_out_time)->format('H:i') ?? '-' }}</td>
                        <td>
                            @if ($attendance->break_start && $attendance->break_end)
                                {{ \Carbon\Carbon::parse($attendance->break_end)->diffInHours($attendance->break_start) }}h
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($attendance->clock_in_time && $attendance->clock_out_time)
                                {{ \Carbon\Carbon::parse($attendance->clock_out_time)->diffInHours($attendance->clock_in_time) }}h
                            @else
                                -
                            @endif
                        </td>
                        <td><a href="#">詳細</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">該当データがありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
