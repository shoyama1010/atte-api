@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">

@section('content')
    <div class="attendance-container">

        <h2>>{{ $staff->name }} さんの勤怠一覧</h2>
        
        {{-- 勤怠テーブル --}}
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
                @forelse ($attendances as $attendance)
                    @php
                        $clockIn = $attendance->clock_in_time
                            ? \Carbon\Carbon::parse($attendance->clock_in_time)
                            : null;
                        $clockOut = $attendance->clock_out_time
                            ? \Carbon\Carbon::parse($attendance->clock_out_time)
                            : null;
                        $breakStart = $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start) : null;
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
                        <td>{{ optional($clockIn)->format('Y/m/d') ?? '-' }}</td>
                        <td>{{ optional($clockIn)->format('H:i') ?? '-' }}</td>
                        <td>{{ optional($clockOut)->format('H:i') ?? '-' }}</td>
                        <td>
                            @if ($breakStart && $breakEnd)
                                {{ $breakStart->diffInHours($breakEnd) }}:{{ str_pad($breakStart->diffInMinutes($breakEnd) % 60, 2, '0', STR_PAD_LEFT) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $workHours }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.edit', ['id' => $attendance->id]) }}">詳細</a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6">該当データがありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- ページネーション --}}
        {{-- <div class="pagination">
            {{ $attendances->links() }}
        </div> --}}

        {{-- 戻るボタン --}}
        <div class="back-btn-area" style="margin-top: 20px;">
            <a href="{{ route('admin.staff.list') }}" class="back-btn">← スタッフ一覧に戻る</a>
        </div>

    </div>
@endsection
