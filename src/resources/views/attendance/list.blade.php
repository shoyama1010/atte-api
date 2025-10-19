@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
    <h2>勤務一覧：{{ $user->name }} </h2>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>休憩開始</th>
                <th>休憩終了</th>
                <th>退勤</th>
                <th>ステータス</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->created_at->format('Y/m/d') }}</td>
                <td>{{ optional($attendance->clock_in_time)->format('H:i') ?? '-' }}</td>
                <td>{{ optional($attendance->break_start)->format('H:i') ?? '-' }}</td>
                <td>{{ optional($attendance->break_end)->format('H:i') ?? '-' }}</td>
                <td>{{ optional($attendance->clock_out_time)->format('H:i') ?? '-' }}</td>
                <td>
                    @switch($attendance->status)
                        @case('none') 未出勤 @break
                        @case('working') 勤務中 @break
                        @case('on_break') 休憩中 @break
                        @case('left') 退勤済み @break
                    @endswitch
                </td>
                <td>
                    <a href="{{ route('attendance.detail', $attendance->id) }}" class="btn-detail">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination">
        {{ $attendances->links() }}
    </div>
</div>
@endsection

