@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
    <div class="attendance-list-container">
        <h2>勤務一覧：{{ Auth::user()->name }} </h2>

        {{-- 🔸 月切替バー --}}
        <div class="month-bar">
            <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="month-arrow">&lt; 前月</a>

            <div class="month-center">
                <i class="fa-regular fa-calendar"></i>
                <span class="month-text">{{ \Carbon\Carbon::parse($month)->format('Y年m月') }}</span>
            </div>

            <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="month-arrow">翌月 &gt;</a>
        </div>

        @if ($attendances->isEmpty())
            <p>現在、勤務記録はありません。</p>
        @else
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            {{-- 社員名 --}}
                            {{-- <td>{{ $attendance->user->name }}</td> --}}
                            <td>{{ $attendance->created_at->format('Y/m/d') }}</td>

                            {{-- 出勤・退勤 --}}
                            <td>{{ optional($attendance->clock_in_time)->format('H:i') ?? '-' }}</td>
                            <td>{{ optional($attendance->clock_out_time)->format('H:i') ?? '-' }}</td>

                            {{-- 🔹 モデルで計算した休憩合計 --}}
                            <td>{{ $attendance->total_rest_time ?? '00:00' }}</td>

                            {{-- 🔹 モデルで計算した勤務合計（出勤〜退勤−休憩） --}}
                            <td>{{ $attendance->working_duration ?? '00:00' }}</td>

                            {{-- 詳細ボタン --}}
                            <td>
                                {{-- <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a> --}}
                                <a href="{{ route('attendance.request.edit', $attendance->id) }}" class="btn-edit">
    詳細
</a>

                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        @endif
    </div>
@endsection
